<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use GuzzleHttp\Client;
use Shucream0117\PhalconLib\Constants\ContentType;

class Imgix
{
    private string $apiKey;
    private Client $client;

    protected const PURGER_API_ENDPOINT = 'https://api.imgix.com/v2/image/purger';

    public function __construct(string $apiKey, Client $client)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    private function isImgixUrl(string $url): bool
    {
        $parsed = parse_url($url, PHP_URL_HOST);
        return strpos($parsed, 'imgix.net') !== false;
    }

    /**
     * imgixのキャッシュを消去する
     * @param string $url
     */
    public function purge(string $url): void
    {
        if (!$this->isImgixUrl($url)) {
            return;
        }
        $this->client->post(static::PURGER_API_ENDPOINT, [
            'headers' => ['ContentType' => ContentType::JSON],
            'json' => ['url' => $url],
            'auth' => [$this->apiKey, ''],
        ]);
    }
}
