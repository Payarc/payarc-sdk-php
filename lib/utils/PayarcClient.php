<?php

namespace Payarc\PayarcSdkPhp\utils;

use InvalidArgumentException;
use Payarc\PayarcSdkPhp\utils\services\CoreServiceFactory;

class PayarcClient
{
    private $coreServiceFactory;


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
        $this->base_url .= ($api_version == '/v1/') ? '/v1/' : '/v' . trim($api_version, '/') . '/';
        $this->version = $version;
        $this->bearer_token_agent = $bearer_token_agent;
    }

    public function __get($name)
    {
        return $this->getService($name);
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
    public function getService($name): services\BaseService|services\BaseServiceFactory|null
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->getService($name);
    }
}