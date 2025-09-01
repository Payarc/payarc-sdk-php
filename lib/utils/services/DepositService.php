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
    public function listSummaryByAgent(mixed $options): array
    {
        $params = [
            'from_date' => $options['from_date'] ?? '',
            'to_date' => $options['to_date'] ?? '',
        ];

        try {
            $response = $this->client->request('GET', 'agent/deposit/summary', [
                'query' => $params
            ], $this->headers);
            $data = json_decode($response->getBody(), true);

            return $this->addObjectId($data['data']);

        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API List Agent deposits'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API List Agent deposits'], $err), $err->getCode());
        }
    }

}