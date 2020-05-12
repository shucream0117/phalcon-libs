<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\Facebook;

class AccessToken
{
    private string $accessToken;
    private string $tokenType;
    private int $expiresIn;

    public function __construct(string $accessToken, string $tokenType, int $expiresIn)
    {
        $this->accessToken = $accessToken;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }
}
