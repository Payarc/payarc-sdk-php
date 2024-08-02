<?php

namespace Payarc\PayarcSdkPhp;
use InvalidArgumentException;
use Payarc\PayarcSdkPhp\utils\Charge;
use Payarc\PayarcSdkPhp\utils\PayarcClient;

class Payarc extends PayarcClient
{
    public function __toString()
    {
        $baseUrl = $this->getBaseUrl();
        $bearerToken = $this->getBearerToken();
        $baseUrl = $baseUrl ? $baseUrl : 'N/A';
        $bearerToken = $bearerToken ? $bearerToken : 'N/A';

        return sprintf("Bearer token: %s, Base URL: %s\n", $bearerToken, $baseUrl);
    }

}