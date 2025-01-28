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
        return $this->handleRequest('POST', '/Login', $requestBody);
    }

    /**
     * @throws Exception
     */
    public function sale($saleData)
    {
        return $this->handleRequest('POST', 'sale', $saleData);
    }

    /**
     * @throws Exception
     */
    public function void($voidData)
    {
        return $this->handleRequest('POST', 'void', $voidData);
    }

    /**
     * @throws Exception
     */
    public function refund($refundData)
    {
        return $this->handleRequest('POST', 'refund', $refundData);
    }

    /**
     * @throws Exception
     */
    public function blindCredit($creditData)
    {
        return $this->handleRequest('POST', 'blindCredit', $creditData);
    }

    /**
     * @throws Exception
     */
    public function auth($authData)
    {
        return $this->handleRequest('POST', 'auth', $authData);
    }

    /**
     * @throws Exception
     */
    public function postAuth($postAuthData)
    {
        return $this->handleRequest('POST', 'postAuth', $postAuthData);
    }

    /**
     * @throws Exception
     */
    public function lastTransaction($transactionData)
    {
        return $this->handleRequest('POST', 'lastTransaction', $transactionData);
    }

    /**
     * @throws Exception
     */
    public function serverInfo()
    {
        return $this->handleRequest('GET', 'serverInfo');
    }

    /**
     * @throws Exception
     */
    public function terminals()
    {
        return $this->handleRequest('GET', 'terminals');
    }

    /**
     * Handles the request to the Payarc Connect API.
     *
     * @throws Exception
     */
    private function handleRequest($method, $endpoint, $data = [])
    {
        try {
            $seed = ['source' => "Payarc Connect $endpoint"];
            $response = $this->client->payarcConnectRequest($method, $endpoint, [
                'json' => $data,
            ]);
            $responseData = json_decode($response->getBody(), true);
            $accessToken = $responseData['BearerTokenInfo']['AccessToken'] ?? null;

            if ($accessToken !== null) {
                return $responseData;
            } else {
                $pcError = new Exception($responseData['ErrorMessage'], $responseData['ErrorCode']);
                throw new Exception($this->manageError($seed, $pcError, false), $pcError->getCode());
            }
            
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError($seed, $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError($seed, $err), $err->getCode());
        }
    }
}