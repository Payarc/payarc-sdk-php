<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class BatchService extends BaseService
{

    /**
     * @throws Exception
     */
    public function list($searchData = []): array
    {
        return $this->listBatchReportsByAgent($searchData);
    }

    /**
     * @throws Exception
     */
    public function retrieve($searchData = []): array
    {
        return $this->listBatchReportDetailsByAgent($searchData);
    }

    /**
     * @throws Exception
     */
    public function listBatchReportsByAgent($searchData = []): array
    {
        $from_date = $searchData['from_date'] ?? [];
        $to_date = $searchData['to_date'] ?? [];

        $params = array_merge(['from_date' => $from_date, 'to_date' => $to_date]);

        try {
            $response = $this->client->agentRequest('GET', 'agent/batch/reports', [
                'query' => $params
            ], $this->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API List batch reports by agent'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API List batch reports by agent'], $err), $err->getCode());
        }
    }
    /**
     * @throws Exception
     */
    public function listBatchReportDetailsByAgent($searchData = []): array
    {
        $merchant_account_number = $searchData['merchant_account_number'] ?? [];
        $reference_number = $searchData['reference_number'] ?? [];
        $date = $searchData['date'] ?? [];

        $params = array_merge(['$reference_number' => $reference_number, 'date' => $date]);

        try {
            $response = $this->client->agentRequest('GET', "agent/batch/reports/details/{$merchant_account_number}", [
                'query' => $params
            ], $this->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API List batch report details by agent'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API List batch report details by agent'], $err), $err->getCode());
        }
    }
}