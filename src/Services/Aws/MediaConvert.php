<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\Aws;

use Aws\MediaConvert\MediaConvertClient;
use Aws\Result;
use Shucream0117\PhalconLib\Exceptions\InvalidApiResponseFormatException;
use Shucream0117\PhalconLib\Services\AbstractService;

class MediaConvert extends AbstractService
{
    private MediaConvertClient $client;

    const STATUS_COMPLETE = 'COMPLETE';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_ERROR = 'ERROR';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_CANCELED = 'CANCELED';

    public function __construct(MediaConvertClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $setting 設定項目が多くて抽象化するのがつらいので雑にAWS規定の連想配列渡す感じですみません
     * @return Result
     */
    public function enqueueMediaConvertJob(array $setting): Result
    {
        return $this->client->createJob($setting);
    }

    /**
     * @param string $jobId
     * @return Result
     */
    public function getJobById(string $jobId): Result
    {
        if ($result = $this->client->getJob(['Id' => $jobId])) {
            return $result;
        }
        throw new InvalidApiResponseFormatException();
    }

    /**
     * @param string $jobId
     * @return Result
     */
    public function cancelJobById(string $jobId): Result
    {
        if ($result = $this->client->cancelJob(['Id' => $jobId])) {
            return $result;
        }
        throw new InvalidApiResponseFormatException();
    }

    /**
     * @param Result $result
     * @return string
     */
    public function getEncodingStatusFromResultObject(Result $result): string
    {
        if (!$jobStatus = ($result->get('Job')['Status'] ?? null)) {
            throw new InvalidApiResponseFormatException('key=Job.Status is not found in the response');
        }
        return $jobStatus;
    }
}
