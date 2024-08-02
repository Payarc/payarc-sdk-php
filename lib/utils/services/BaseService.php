<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use InvalidArgumentException;

abstract class BaseService
{
    protected $client;
    public function __construct($client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    private static function formatParams($params)
    {
        if (null === $params) {
            return null;
        }
        \array_walk_recursive($params, function (&$value, $key) {
            if (null === $value) {
                $value = '';
            }
        });

        return $params;
    }

    protected function request($method, $path, $params, $opts)
    {
        return $this->getClient()->request($method, $path, self::formatParams($params), $opts);
    }

    protected function buildPath($basePath, ...$ids): string
    {
        foreach ($ids as $id) {
            if (null === $id || '' === \trim($id)) {
                $msg = 'The resource ID cannot be null or whitespace.';

                throw new InvalidArgumentException($msg);
            }
        }

        return \sprintf($basePath, ...\array_map('\urlencode', $ids));
    }
}