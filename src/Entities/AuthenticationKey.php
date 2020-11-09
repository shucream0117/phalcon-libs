<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities;

class AuthenticationKey
{
    private string $key;
    private int $expiresIn; // 失効日時のunixtime

    public function __construct(string $authKey, int $expiresIn)
    {
        $this->key = $authKey;
        $this->expiresIn = $expiresIn;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }
}
