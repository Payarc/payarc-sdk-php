<?php

namespace Payarc\PayarcSdkPhp\utils\services;

abstract class BaseServiceFactory
{
   private $client;
   private $services;
    private $classMap;

    public function __construct($client)
    {
        $this->client = $client;
        $this->services = [];
        $this->classMap = [];
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    abstract protected function getServiceClass(string $name): string|array|null;

    /**
     * @param string $name
     * @return null|BaseService|BaseServiceFactory
     */
    public function __get(string $name)
    {
        return $this->getService($name);
    }

    /**
     * Get the service or nested factory by name.
     *
     * @param string $name
     * @return null|BaseService|BaseServiceFactory
     */
    public function getService(string $name)
    {
        if (array_key_exists($name, $this->services)) {
            return $this->services[$name];
        }

        $serviceClass = $this->classMap[$name] ?? null;

        if (is_array($serviceClass)) {
            // If it's a nested service, create a new service factory for the nested services
            $nestedFactory = new static($this->client);
            $nestedFactory->setClassMap($serviceClass);
            $this->services[$name] = $nestedFactory;
            return $nestedFactory;
        } elseif ($serviceClass !== null) {
            // If it's a regular service, instantiate and cache it
            $this->services[$name] = new $serviceClass($this->client);
            return $this->services[$name];
        }

        trigger_error('Undefined property: ' . static::class . '::$' . $name, E_USER_NOTICE);

        return null;
    }

    /**
     * Set the class map for the current service factory.
     *
     * @param array $classMap
     */
    public function setClassMap(array $classMap): void
    {
        $this->classMap = $classMap;
    }

}