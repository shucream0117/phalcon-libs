<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

interface EmailTransmitterInterface
{
    public function sendTextMessage(string $to, string $subject, string $body);
    public function sendTextMessageToMany(array $to, string $subject, string $body);
}
