<?php

namespace Payarc\PayarcSdkPhp\utils\services;

abstract class BaseServiceFactory
{
   private $client;
   private $services;

    public function __construct($client)
    {
        $this->client = $client;
        $this->services = [];
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    abstract protected function getServiceClass(string $name): ?string;

    /**
     * @param string $name
     *
     * @return null|BaseService|BaseServiceFactory
     */
    public function __get(string $name)
    {
        return $this->getService($name);
    }

    /**
     * @param string $name
     *
     * @return null|BaseService|BaseServiceFactory
     */
    public function getService(string $name)
    {
        $serviceClass = $this->getServiceClass($name);
        if (null !== $serviceClass) {
            if (!\array_key_exists($name, $this->services)) {
                $this->services[$name] = new $serviceClass($this->client);
            }

            return $this->services[$name];
        }

        \trigger_error('Undefined property: ' . static::class . '::$' . $name);

        return null;
    }
}