<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class PayarcConnectService extends BaseService
{
    /**
     * @throws Exception
     */
    public function login()
    {
        $requestBody = [
            'SecretKey' => $this->client->getBearerToken()
        ];

        $responseData = $this->handleRequest('POST', '/Login', $requestBody);
        $accessToken = $responseData['BearerTokenInfo']['AccessToken'] ?? null;

        if ($accessToken !== null) {
            $this->client->setPayarcConnectAccessToken($accessToken);
            return $responseData;
        } else {
            $pcError = new Exception($responseData['ErrorMessage'], $responseData['ErrorCode']);
            throw new Exception($this->manageError(['source' => "Payarc Connect Login"], $pcError, false), $pcError->getCode());
        }

    }

    /**
     * @throws Exception
     */
    public function sale($tenderType, $ecrRefNum, $amount, $deviceSerialNo)
    {
        $requestBody = [
            'TenderType' => $tenderType,
            'TransType' => "SALE",
            'ECRRefNum' => $ecrRefNum,
            'Amount' => $amount,
            'DeviceSerialNo' => $deviceSerialNo
        ];

        return $this->handleRequest('POST', '/Transactions', $requestBody);
    }

    /**
     * @throws Exception
     */
    public function void($payarcTransactionId, $deviceSerialNo)
    {
        $requestBody = [
            'TransType' => 'VOID',
            'PayarcTransactionId' => $payarcTransactionId,
            'DeviceSerialNo' => $deviceSerialNo
        ];
        return $this->handleRequest('POST', '/Transactions', $requestBody);
    }

    /**
     * @throws Exception
     */
    public function refund($amount, $payarcTransactionId, $deviceSerialNo)
    {
        $requestBody = [
            'TransType' => 'REFUND',
            'Amount' => $amount,
            'PayarcTransactionId' => $payarcTransactionId,
            'DeviceSerialNo' => $deviceSerialNo
        ];
        return $this->handleRequest('POST', '/Transactions', $requestBody);
    }

    /**
     * @throws Exception
     */
    public function blindCredit($ecrRefNum, $amount, $token, $expDate, $deviceSerialNo)
    {
        $requestBody = [
            'TransType' => 'RETURN',
            'ECRRefNum' => $ecrRefNum,
            'Amount' => $amount,
            'Token' => $token,
            'ExpDate' => $expDate,
            'DeviceSerialNo' => $deviceSerialNo,
        ];
        return $this->handleRequest('POST', '/Transactions', $requestBody);
    }

    /**
     * @throws Exception
     */
    public function auth($ecrRefNum, $amount, $deviceSerialNo)
    {
        $requestBody = [
            'TransType' => 'AUTH',
            'ECRRefNum' => $ecrRefNum,
            'Amount' => $amount,
            'DeviceSerialNo' => $deviceSerialNo,
        ];
        return $this->handleRequest('POST', '/Transactions', $requestBody);
    }

    /**
     * @throws Exception
     */
    public function postAuth($ecrRefNum, $origRefNum, $amount, $deviceSerialNo)
    {
        $requestBody = [
            'TransType' => 'POSTAUTH',
            'ECRRefNum' => $ecrRefNum,
            'OrigRefNum' => $origRefNum,
            'Amount' => $amount,
            'DeviceSerialNo' => $deviceSerialNo,
        ];
        return $this->handleRequest('POST', '/Transactions', $requestBody);
    }

    /**
     * @throws Exception
     */
    public function lastTransaction($deviceSerialNo)
    {
        return $this->handleRequest('GET', '/LastTransaction?DeviceSerialNo=' . $deviceSerialNo, $deviceSerialNo);
    }

    /**
     * @throws Exception
     */
    public function serverInfo()
    {
        return $this->handleRequest('GET', '/ServerInfo', serverInfoBypass:true);
    }

    /**
     * @throws Exception
     */
    public function terminals()
    {
        return $this->handleRequest('GET', '/Terminals');
    }

    /**
     * Handles the request to the Payarc Connect API.
     *
     * @throws Exception
     */
    private function handleRequest($method, $endpoint, $data = [], $serverInfoBypass = false)
    {
        try {
            $seed = ['source' => "Payarc Connect $endpoint"];
            $response = $this->client->payarcConnectRequest($method, $endpoint, [
                'json' => $data,
            ]);

            $responseData = json_decode($response->getBody(), true);
            $errorCode = $responseData['ErrorCode'] ?? -1;
            $errorMessage = $responseData['ErrorMessage'] ?? 'unKnown';

            if ($serverInfoBypass || $errorCode === 0) {
                return $responseData;
            } else {
                $pcError = new PayarcConnectException($errorMessage, $errorCode);
                throw new PayarcConnectException($this->manageError($seed, $pcError, false), $pcError->getCode());
            }

        } catch (PayarcConnectException $err) {
            throw $err;
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError($seed, $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError($seed, $err), $err->getCode());
        }
    }
}

class PayarcConnectException extends Exception
{
}