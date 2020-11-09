<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\Google;

class AccessToken
{
    private array $raw;

    public function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    public function getAccessToken(): string
    {
        return $this->raw['access_token'];
    }

    public function getRefreshToken(): string
    {
        return $this->raw['refresh_token'];
    }

    public function getRaw(): array
    {
        return $this->raw;
    }
}
