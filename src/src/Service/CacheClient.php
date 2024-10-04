<?php

namespace App\Service;

use Predis\Client;
use Predis\Response\Status;

class CacheClient
{
    public function __construct(private readonly Client $client)
    {
    }

    public function set(string $key, string $value, int $expiration = 0): Status
    {
        if ($expiration > 0) {
            return $this->client->setex($key, $expiration, $value);
        }

        return $this->client->set($key, $value);
    }

    public function get(string $key): ?string
    {
        return $this->client->get($key);
    }

    public function delete(string $key): int
    {
        return $this->client->del($key);
    }
}
