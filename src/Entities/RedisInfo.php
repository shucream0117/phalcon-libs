<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities;

class RedisInfo
{
    private string $host;
    private int $port;
    private int $index;

    public function __construct(string $host, int $port, int $index)
    {
        $this->host = $host;
        $this->port = $port;
        $this->index = $index;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getIndex(): int
    {
        return $this->index;
    }
}