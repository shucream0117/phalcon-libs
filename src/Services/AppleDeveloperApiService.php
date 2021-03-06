<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use GuzzleHttp\Client;
use Shucream0117\PhalconLib\Utils\Json;
use Shucream0117\PhalconLib\Utils\StringUtil;

class AppleDeveloperApiService extends AbstractService
{
    private string $sharedSecret;
    private bool $isSandbox;
    private Client $httpClient;

    private const SANDBOX_API_URL = 'https://sandbox.itunes.apple.com';
    private const PRODUCTION_API_URL = 'https://buy.itunes.apple.com';

    public function __construct(string $sharedSecret, bool $isSandbox = true, ?Client $httpClient = null)
    {
        $this->sharedSecret = $sharedSecret;
        $this->isSandbox = $isSandbox;
        $this->httpClient = is_null($httpClient) ? new Client() : $httpClient;
    }

    /**
     * レシートを検証する
     *
     * @param string $base64EncodedReceipt
     * @param bool $excludeOldTransactions
     * @return array
     */
    public function verifyReceipt(string $base64EncodedReceipt, bool $excludeOldTransactions = true)
    {
        $response = $this->httpClient->post($this->getUrl('/verifyReceipt'), [
            'json' => [
                'receipt-data' => $base64EncodedReceipt,
                'password' => $this->sharedSecret,
                'exclude-old-transactions' => $excludeOldTransactions,
            ],
        ]);;
        return Json::decode($response->getBody()->getContents());
    }

    private function getUrl(string $path): string
    {
        $base = $this->isSandbox ? self::SANDBOX_API_URL : self::PRODUCTION_API_URL;
        return StringUtil::concat('/', $base, $path);
    }
}
