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

    protected const STATUS_PROCESSING = 'PROCESSING';
    protected const STATUS_ERROR = 'ERROR';
    protected const STATUS_SUBMITTED = 'SUBMITTED';

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
     * @throws InvalidApiResponseFormatException
     */
    public function getEncodingStatus(string $jobId): string
    {
        $job = $this->client->getJob(['Id' => $jobId]);
        if (!$jobStatus = ($job->get('Job')['Status'] ?? null)) {
            throw new InvalidApiResponseFormatException('key=Job.Status is not found in the response');
        }
        return $jobStatus;
    }
}
