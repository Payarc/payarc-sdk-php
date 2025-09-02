<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class DepositService extends BaseService
{
    /**
     * @throws Exception
     */
    public function agentDepositSummary(mixed $options)
    {
        try {
            $response = $this->client->request('GET', 'agent/deposit/summary', [
                'query' => $options
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return [
                'deposits' => $this->addObjectId($data['data']),
            ];

        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API List Agent deposits summary'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API List Agent deposits summary'], $err), $err->getCode());
        }
    }

}