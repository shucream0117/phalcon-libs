<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\Twitter;

class AccountSetting
{
    private string $timezoneName; // Asia/Tokyo みたいなやつ
    private int $timezoneUtcOffset;
    private string $language; // ja など

    public function __construct(string $timezoneName, int $utfOffset, string $language)
    {
        $this->timezoneName = $timezoneName;
        $this->timezoneUtcOffset = $utfOffset;
        $this->language = $language;
    }
}
