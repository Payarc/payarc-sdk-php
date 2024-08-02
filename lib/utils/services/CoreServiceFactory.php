<?php

namespace Payarc\PayarcSdkPhp\utils\services;

class CoreServiceFactory extends BaseServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = [
        'charges' => ChargeService::class,
    ];

    protected function getServiceClass($name): ?string
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}