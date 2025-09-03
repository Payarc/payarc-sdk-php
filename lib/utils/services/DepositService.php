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
     * Retrieves a list of all agent deposits summary.
     *
     * @param array $options
     * @return array{deposits: mixed}
     * @throws Exception
     */
    public function list($options)
    {
        return $this->agentDepositSummary($options);
    }

    /**@return array{deposits: mixed}
     * @throws Exception
     */
    private function agentDepositSummary(mixed $options)
    {
        $headers = $this->headers;
        $headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();

        //if from_date and to_date are not set, set start and end of the month
        if (!isset($options['from_date']) || !isset($options['to_date'])) {
            $options['from_date'] = date('Y-m-01');
            $options['to_date'] = date('Y-m-t');
        }

        try {

            $response = $this->client->request('GET', 'agent/deposit/summary', [
                'query' => $options
            ], $headers);
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