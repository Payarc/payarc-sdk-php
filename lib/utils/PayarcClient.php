<?php

namespace Payarc\PayarcSdkPhp\utils;

use InvalidArgumentException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class PayarcClient
{
    private Client $client;

    private $url_map = [
        'prod'=> 'https://api.payarc.net',
        'sandbox'=> 'https://testapi.payarc.net'
    ];
    private $bearer_token;
    private $base_url;
    private $version;
    private $bearer_token_agent;
    public function __construct($bearer_token, $base_url='sandbox', $api_version='/v1/', $version='1.0', $bearer_token_agent=null)
    {
        if (!$bearer_token) {
            throw new InvalidArgumentException('Bearer token is required');
        }
        $this->bearer_token = $bearer_token;
        $this->base_url = $this->url_map[$base_url] ?? $base_url;
        $this->base_url .= ($api_version === '/v1/') ? '/v1/' : '/v' . trim($api_version, '/') . '/';
        $this->version = $version;
        $this->bearer_token_agent = $bearer_token_agent;
        $this->client = new Client();
    }

    public function getBaseUrl(): string
    {
        return $this->base_url;
    }
    public function getVersion()
    {
        return $this->version;
    }
    public function getBearerToken()
    {
        return $this->bearer_token;
    }
    public function getBearerTokenAgent()
    {
        return $this->bearer_token_agent;
    }


    /**
     * @throws GuzzleException
     */
    public function request($method, $path, $params, $headers)
    {
        return $this->client->request($method, $this->base_url . $path,[
            'headers' => $headers,
            ...$params
        ]);
    }

}