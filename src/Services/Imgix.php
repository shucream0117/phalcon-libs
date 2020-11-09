<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use GuzzleHttp\Client;
use Imgix\UrlBuilder;
use Shucream0117\PhalconLib\Constants\MimeType;

class Imgix
{
    const FIT_CROP = 'crop';
    const FIT_CLIP = 'clip';
    const FIT_MAX = 'max';

    const FORMAT_JPG = 'jpg';
    const FORMAT_PNG = 'png';
    const FORMAT_GIF = 'gif';
    const FORMAT_WEBP = 'webp';

    const FORMAT_MP4 = 'mp4';
    const FORMAT_WEBM = 'webm';

    private string $apiKey;
    private Client $client;

    protected const PURGER_API_ENDPOINT = 'https://api.imgix.com/api/v1/purge';

    public function __construct(string $apiKey, ?Client $client = null)
    {
        $this->apiKey = $apiKey;
        $this->client = $client !== null ? $client : new Client();
    }

    private function isImgixUrl(string $url): bool
    {
        $parsed = parse_url($url, PHP_URL_HOST);
        return strpos($parsed, 'imgix.net') !== false;
    }

    /**
     * imgixのキャッシュを消去する
     * @param string $url
     * @param bool $subImage
     */
    public function purge(string $url, bool $subImage = true): void
    {
        if (!$this->isImgixUrl($url)) {
            return;
        }
        $this->client->post(static::PURGER_API_ENDPOINT, [
            'headers' => [
                'ContentType' => MimeType::JSON,
                'Authorization' => "Bearer {$this->apiKey}"
            ],
            'json' => [
                'data' => [
                    'type' => 'purges',
                    'attributes' => [
                        'url' => $url,
                        'sub_image' => $subImage,
                    ],
                ],
            ],
        ]);
    }

    public static function createUrlBuilder(
        string $domain,
        bool $useHttps = true,
        string $signatureToken = '',
        bool $includeLibParam = false
    ): UrlBuilder {
        return new UrlBuilder($domain, $useHttps, $signatureToken, $includeLibParam);
    }
}
