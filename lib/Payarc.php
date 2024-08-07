<?php

namespace Payarc\PayarcSdkPhp;
use InvalidArgumentException;
use Payarc\PayarcSdkPhp\utils\PayarcClient;
use Payarc\PayarcSdkPhp\utils\services\CoreServiceFactory;
use Payarc\PayarcSdkPhp\utils\services\BaseServiceFactory;

class Payarc extends PayarcClient
{

    private $coreServiceFactory;

    public function __get($name)
    {
        return $this->getService($name);
    }

    public function getService($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->getService($name);
    }
    public function __toString()
    {
        $baseUrl = $this->getBaseUrl();
        $bearerToken = $this->getBearerToken();
        $baseUrl = $baseUrl ? $baseUrl : 'N/A';
        $bearerToken = $bearerToken ? $bearerToken : 'N/A';

        return sprintf("Bearer token: %s, Base URL: %s\n", $bearerToken, $baseUrl);
    }

}