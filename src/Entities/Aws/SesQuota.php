<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\Aws;

use Aws\Result;

class SesQuota
{
    private int $max24HourSend;
    private int $maxSendRate;
    private int $sentLast24Hours;
    private Result $rawData;

    public function __construct(int $max24HourSend, int $maxSendRate, int $sentLast24Hours, Result $rawData)
    {
        $this->max24HourSend = $max24HourSend;
        $this->maxSendRate = $maxSendRate;
        $this->sentLast24Hours = $sentLast24Hours;
        $this->rawData = $rawData;
    }

    public function getMax24HourSend(): int
    {
        return $this->max24HourSend;
    }

    public function getMaxSendRate(): int
    {
        return $this->maxSendRate;
    }

    public function getSentLast24Hours(): int
    {
        return $this->sentLast24Hours;
    }

    public function getRawData(): Result
    {
        return $this->rawData;
    }
}
