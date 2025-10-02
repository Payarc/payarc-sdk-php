<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class PayeeService extends BaseService
{
    /**
     * @throws Exception
     */
    public function create($payeeData)
    {
        return $this->createPayee($payeeData);
    }

    /**
     * @throws Exception
     */
    public function list($searchData = [])
    {
        return $this->listPayees($searchData);
    }

    /**
     * @param string|array $payee
     * @throws Exception
     */
    public function delete($payee)
    {
        return $this->deletePayee($payee);
    }

    /**
     * @throws Exception
     */
    protected function createPayee($payeeData)
    {
        try {
            $response = $this->client->agentRequest('POST', 'agent-hub/apply/payees', [
                'json' => $payeeData
            ], $this->headers);

            $data = json_decode($response->getBody(), true);

            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API create payee'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API create payee'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    protected function listPayees($searchData)
    {
        $include = $searchData['include'] ?? 'appData';

        try {
            $response = $this->client->agentRequest('GET', 'agent-hub/apply/payees', [
                'query' => ['include' => $include]
            ], $this->headers);

            $data = json_decode($response->getBody(), true);

            $payees = array_map(function ($payee) {
                return $this->addObjectId($payee);
            }, $data ?? []);

            return ['payees' => $payees];
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API list payees by agent'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API list payees by agent'], $err), $err->getCode());
        }
    }

    /**
     * @param array|string|null $payee
     * @throws Exception
     */
    protected function deletePayee($payee)
    {
        $payeeId = is_array($payee) ? ($payee['object_id'] ?? '') : $payee;

        if (str_starts_with($payeeId, 'appy_')) {
            $payeeId = substr($payeeId, 5);
        }

        try {
            $response = $this->client->agentRequest('DELETE', "agent-hub/apply/payees/{$payeeId}", [],
                $this->headers
            );

            if ($response->getStatusCode() === 204) {
                return [];
            }

            $data = json_decode($response->getBody(), true);

            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API delete payee'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API delete payee'], $err), $err->getCode());
        }
    }
}
