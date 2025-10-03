<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class PayeeService extends BaseService
{
    public function create($params)
    {
        return $this->addPayee($params);
    }

    public function list($params = [])
    {
        return $this->listPayees($params);
    }

    public function delete($payee)
    {
        return $this->deletePayee($payee);
    }

    /**
     * @throws Exception
     */
    private function addPayee($params)
    {
        $headers = $this->headers;
        $headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();

        try {
            $response = $this->client->request('POST', 'agent-hub/apply/payees', [
                'json' => $params
            ], $headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Add payee'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Add payee'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    private function listPayees($params)
    {
        $headers = $this->headers;
        $headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();

        try {
            $include = $params['include'] ?? 'appData';

            $response = $this->client->request('GET', 'agent-hub/apply/payees', [
                'query' => [
                    'include' => $include
                ]
            ], $headers);

            $data = json_decode($response->getBody()->getContents(), true);

            $payees = array_map([$this, 'addObjectId'], $data['data'] ?? []);
            $pagination = $data['meta']['pagination'] ?? [];
            unset($pagination['links']);
            return [
                'payees' => $payees,
                'pagination' => $pagination
            ];

        } catch (ClientException|ServerException $err) {
//            var_dump($err);die();
            throw new Exception($this->manageError(['source' => 'API List payees'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
//            var_dump($err);die();
            throw new Exception($this->manageError(['source' => 'API List payees'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    private function deletePayee($payee)
    {
        $payeeId = $payee['object_id'] ?? $payee;
        if (is_string($payeeId) && str_starts_with($payeeId, 'appl_')) {
            $payeeId = substr($payeeId, 5);
        }

        $headers = $this->headers;
        $headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();

        try {
            $response = $this->client->request('DELETE', 'agent-hub/apply/payees/' . $payeeId, [], $headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Delete payee'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Delete payee'], $err), $err->getCode());
        }
    }

}