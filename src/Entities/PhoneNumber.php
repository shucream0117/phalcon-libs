<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities;

class PhoneNumber
{
    private string $phoneCountryCode;
    private string $phoneNumber;

    public function __construct(string $phoneCountryCode, string $phoneNumber)
    {
        $this->phoneCountryCode = $phoneCountryCode;
        $this->phoneNumber = $phoneNumber;
    }

    public function getPhoneCountryCode(): string
    {
        return $this->phoneCountryCode;
    }

    public function getPhoneNumber(): string
    {
        return self::sanitizePhoneNumber($this->phoneNumber);
    }

    public function getFullyQualifiedPhoneNumber(): string
    {
        return "+{$this->getPhoneCountryCode()}{$this->getPhoneNumber()}";
    }

    /**
     * 先頭にゼロがある場合は削る
     * 08012345678 → 8012345678
     *
     * @param string $phoneNumber
     * @return string
     */
    private static function sanitizePhoneNumber(string $phoneNumber): string
    {
        return preg_replace('/^0(\d0)/', '$1', $phoneNumber);
    }
}
