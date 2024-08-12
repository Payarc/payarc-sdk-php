<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class ChargeService extends BaseService
{
    /**
     * @throws Exception
     */
    public function create($obj, $charge_data = null)
    {
        return $this->createCharge($obj, $charge_data);
    }

    /**
     * @throws Exception
     */
    public function retrieve($chargeId)
    {
        return $this->getCharge($chargeId);
    }

    /**
     * @throws Exception
     */
    public function list($options = []): array
    {
        return $this->listCharges($options);
    }

    /**
     * @throws Exception
     */
    public function createRefund($charge, $params)
    {
        return $this->refundCharge($charge, $params);
    }


    /**
     * @throws Exception
     */
    public function createCharge($obj, $charge_data = null)
    {
        try {
            $charge_data = $charge_data ?? $obj;
            if (isset($charge_data['source'])) {
                if (is_array($charge_data['source']) && count($charge_data['source']) > 0) {
                    $charge_data = array_merge($charge_data, $charge_data['source']);
                }
            }
            if (isset($obj['object_id'])) {
                $charge_data['customer_id'] = str_starts_with($obj['object_id'], 'cus_') ? substr($obj['object_id'], 4) : $obj['object_id'];
            }
            if (isset($charge_data['source'])) {
                $source = $charge_data['source'];
                $isstr = !is_array($charge_data['source']);
                switch (true) {
                    case $isstr && str_starts_with($source, 'tok_'):
                        $charge_data['token_id'] = substr($source, 4);
                        break;
                    case $isstr && str_starts_with($source, 'cus_'):
                        $charge_data['customer_id'] = substr($source, 4);
                        break;
                    case $isstr && str_starts_with($source, 'card_'):
                        $charge_data['card_id'] = substr($source, 5);
                        break;
                    case ($isstr && str_starts_with($source, 'bnk_')) || isset($charge_data['sec_code']):
                        $charge_data['bank_account_id'] = str_starts_with($source, 'bnk_') ? substr($source, 4) : $charge_data['bank_account_id'];
                        $charge_data['type'] = 'debit';
                        unset($charge_data['source']);
                        return $this->handleCharge('achcharges', $charge_data, $this->headers);
                    case $isstr && preg_match('/^\d/', $source):
                        $charge_data['card_number'] = $source;
                        break;
                }
                unset($charge_data['source']); // Remove source after processing
            }
            $this->normalizeIDs($charge_data, ['token_id' => 3, 'customer_id' => 3, 'card_id' => 4]);
            return $this->handleCharge('charges', $charge_data, $this->headers);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Create Charge'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Create Charge'], $err), $err->getCode());
        }
    }

    private function handleCharge($path, $charge_data, $headers)
    {
        $response = $this->request('POST', $path, ['json' => $charge_data], $headers);
        $data = json_decode($response->getBody(), true);
        return $this->addObjectId($data['data']);
    }

    private function normalizeIDs(&$charge_data, $id_prefixes): void
    {
        foreach ($id_prefixes as $key => $prefix_length) {
            if (isset($charge_data[$key]) && str_starts_with($charge_data[$key], substr($key, 0, $prefix_length) . "_")) {
                $charge_data[$key] = substr($charge_data[$key], $prefix_length + 1);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function getCharge($chargeId)
    {
        try {
            list($endpoint, $chargeId) = $this->determineEndpointAndId($chargeId);
            $response = $this->client->request('GET', $endpoint . '/' . $chargeId, [
                'query' => $this->getParams($endpoint)
            ], $this->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Retrieve Charge Info'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Retrieve Charge Info'], $err), $err->getCode());
        }
    }


    /**
     * @throws Exception
     */
    private function determineEndpointAndId($chargeId): array
    {
        if (str_starts_with($chargeId, 'ch_')) {
            return ['charges', substr($chargeId, 3)];
        } elseif (str_starts_with($chargeId, 'ach_')) {
            return ['achcharges', substr($chargeId, 4)];
        }
        throw new \Exception("Invalid charge ID format.");
    }

    private function getParams($endpoint): array
    {
        if ($endpoint === 'charges') {
            return ['include' => 'transaction_metadata,extra_metadata'];
        } elseif ($endpoint === 'achcharges') {
            return ['include' => 'review'];
        }
        return [];
    }

    /**
     * @throws Exception
     */
    public function listCharges($searchData = []): array
    {
        $limit = $searchData['limit'] ?? 25;
        $page = $searchData['page'] ?? 1;
        $search = $searchData['search'] ?? [];
        $params = array_merge(['limit' => $limit, 'page' => $page], $search);

        try {
            $response = $this->client->request('GET', 'charges', [
                'query' => $params
            ], $this->headers);
            $data = json_decode($response->getBody(), true);
            $charges = array_map([$this, 'addObjectId'], $data['data']);
            $pagination = $data['meta']['pagination'] ?? [];
            unset($pagination['links']);
            return [
                'charges' => $charges,
                'pagination' => $pagination
            ];
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API List charges'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API List charges'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function refundCharge($charge, $params = [])
    {
        $achRegular = false;
        $url = 'charges';
        $msg = '';
        $chargeId = is_array($charge) ? ($charge['object_id'] ?? $charge) : $charge;
        try {
            if (str_starts_with($chargeId, 'ch_')) {
                $chargeId = substr($chargeId, 3);
                $url = "$url/{$chargeId}/refunds";
            }
            if (str_starts_with($chargeId, 'ach_')) {
                $achRegular = true;
                $params = $this->getAchChargeParams($charge, $params);
                $url = 'achcharges';
                $msg = 'ACH';
            }
            $response = $this->client->request('POST', $url, [
                'json' => $params
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return !$achRegular ? $this->addObjectId($data['data']) : $response;
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => "API Refund a $msg charge"], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => "API Refund a $msg charge"], $err), $err->getCode());
        }

    }

    /**
     * @throws Exception
     */
    public function getAchChargeParams($charge, $params = [])
    {
            if (!is_array($charge)) {
                $charge = $this->getCharge($charge);
            }
            $params['type'] = 'credit';
            $params['amount'] = $params['amount'] ?? $charge['amount'];
            $params['sec_code'] = $params['sec_code'] ?? $charge['sec_code'];

            if (isset($charge['bank_account']['data']['object_id'])) {
                $params['bank_account_id'] = $params['bank_account_id'] ?? $charge['bank_account']['data']['object_id'];
            }
            if (isset($params['bank_account_id']) && str_starts_with($params['bank_account_id'], 'bnk_')) {
                $params['bank_account_id'] = substr($params['bank_account_id'], 4);
            }
            return $params;
    }
}