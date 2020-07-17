<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Shucream0117\PhalconLib\Entities\PhoneNumber;

interface SmsTransmitterInterface
{
    public function send(PhoneNumber $phoneNumber, string $message);
}
