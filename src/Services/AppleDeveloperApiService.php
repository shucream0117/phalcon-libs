<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Shucream0117\PhalconLib\Utils\Json;
use Shucream0117\PhalconLib\Utils\StringUtil;

class AppleDeveloperApiService extends AbstractService
{
    private ?string $sharedSecret;
    private bool $isSandbox;
    private Client $httpClient;
    private ?string $privateKeyId;
    private ?string $privateKeyPath;
    private ?string $appBundleId;
    private ?string $issuerId;

    /*
     * App Store Receipts
     * https://developer.apple.com/documentation/appstorereceipts
     */
    private const APP_STORE_RECEIPTS_API_SANDBOX_URL = 'https://sandbox.itunes.apple.com';
    private const APP_STORE_RECEIPTS_API_PRODUCTION_URL = 'https://buy.itunes.apple.com';

    /*
     * App Store Server API
     * https://developer.apple.com/documentation/appstoreserverapi
     */
    private const APP_STORE_SERVER_API_SANDBOX_URL = 'https://api.storekit-sandbox.itunes.apple.com';
    private const APP_STORE_SERVER_API_PRODUCTION_URL = 'https://api.storekit.itunes.apple.com';

    const ALG_ES256 = 'ES256';

    /*
     * サブスク期間延長理由
     * https://developer.apple.com/documentation/appstoreserverapi/extendreasoncode
     */
    const EXTEND_REASON_CODE_UNDECLARED = 0;
    const EXTEND_REASON_CODE_CUSTOMER_SATISFACTION = 1;
    const EXTEND_REASON_CODE_OTHER_REASON = 2;
    const EXTEND_REASON_CODE_SERVICE_ISSUE_OR_OUTAGE = 3;

    public function __construct(
        ?string $sharedSecret = null, // verifyReceipt用
        bool $isSandbox = true,
        ?string $privateKeyId = null, // JWT署名用秘密鍵のID
        ?string $privateKeyPath = null, // JWT署名用秘密鍵
        ?string $appBundleId = null, // アプリ識別子 (例 com.example.testbundleid2021)
        ?string $issuerId = null, // (例 57246542-96fe-1a63-e053-0824d011072a)
        ?Client $httpClient = null
    ) {
        $this->sharedSecret = $sharedSecret;
        $this->isSandbox = $isSandbox;
        $this->privateKeyId = $privateKeyId;
        $this->privateKeyPath = $privateKeyPath;
        $this->appBundleId = $appBundleId;
        $this->issuerId = $issuerId;
        $this->httpClient = is_null($httpClient) ? new Client() : $httpClient;
    }

    /**
     * レシートを検証する
     * https://developer.apple.com/documentation/appstorereceipts/verifyreceipt
     *
     * @param string $base64EncodedReceipt
     * @param bool $excludeOldTransactions
     * @return array
     * @throws \Exception
     */
    public function verifyReceipt(string $base64EncodedReceipt, bool $excludeOldTransactions = true): array
    {
        if (!$this->sharedSecret) {
            throw new \Exception('shared secret is required');
        }
        $response = $this->httpClient->post($this->getAppStoreReceiptsApiUrl('/verifyReceipt'), [
            'json' => [
                'receipt-data' => $base64EncodedReceipt,
                'password' => $this->sharedSecret,
                'exclude-old-transactions' => $excludeOldTransactions,
            ],
        ]);
        return Json::decode($response->getBody()->getContents());
    }

    /**
     * OrderID指定で取得
     * https://developer.apple.com/documentation/appstoreserverapi/look_up_order_id
     *
     * @param string $orderId
     * @return array
     */
    public function getOrder(string $orderId): array
    {
        $response = $this->httpClient->get($this->getAppStoreServerApiUrl("/inApps/v1/lookup/{$orderId}"), [
            'headers' => $this->getAuthorizationHeader(),
        ]);
        return Json::decode($response->getBody()->getContents());
    }

    /**
     * original transaction id 指定でサブスクリプション情報を取得
     * https://developer.apple.com/documentation/appstoreserverapi/get_all_subscription_statuses
     * @param string $originalTxId
     * @return array<string, mixed>
     */
    public function getSubscriptions(string $originalTxId): array
    {
        $response = $this->httpClient->get($this->getAppStoreServerApiUrl("/inApps/v1/subscriptions/{$originalTxId}"), [
            'headers' => $this->getAuthorizationHeader(),
        ]);
        return Json::decode($response->getBody()->getContents());
    }

    /**
     * サブスクリプション延長
     * https://developer.apple.com/documentation/appstoreserverapi/extendrenewaldaterequest
     * @param string $originalTxId
     * @param int $days
     * @param int $reasonCode
     * @return array<string, mixed>
     */
    public function extendSubscriptionRenewalDate(string $originalTxId, int $days, int $reasonCode): array
    {
        $response = $this->httpClient->put($this->getAppStoreServerApiUrl("/inApps/v1/subscriptions/extend/{$originalTxId}"), [
            'headers' => $this->getAuthorizationHeader(),
            'json' => [
                'extendByDays' => $days,
                'extendReasonCode' => $reasonCode,
                'requestIdentifier' => $this->generateRequestIdentifier(),
            ],
        ]);
        return Json::decode($response->getBody()->getContents());
    }

    /**
     * 購入履歴取得
     * @see https://developer.apple.com/documentation/appstoreserverapi/get_transaction_history
     * @param string $originalTxId
     * @param string|null $revision
     * @return array<string, mixed>
     */
    public function getTransactionHistory(string $originalTxId, ?string $revision = null): array
    {
        $response = $this->httpClient->get($this->getAppStoreServerApiUrl("/inApps/v1/history/{$originalTxId}"), [
            'headers' => $this->getAuthorizationHeader(),
            'query' => ['revision' => $revision],
        ]);
        return Json::decode($response->getBody()->getContents());
    }

    /**
     * getTransactionHistory のラッパーで、全ての履歴を取得してマージして返す
     *
     * @param string $originalTxId
     * @return array
     */
    public function getAllRevisionsOfTransactionHistory(string $originalTxId):array
    {
        $revision = null;
        $txList = [];
        while(true) {
            $result = $this->getTransactionHistory($originalTxId, $revision);
            $txList = array_merge($txList, $result['signedTransactions']);
            if (!$result['hasMore']) {
                break;
            }
            $revision = $result['revision'];
        }
        $result['signedTransactions'] = $txList;
        return $result;
    }
    
    /**
     * リクエスト用のJWT作成
     * https://developer.apple.com/documentation/appstoreserverapi/generating_tokens_for_api_requests
     * @return string
     * @throws \Exception
     */
    private function createJwt(): string
    {
        if (!$this->privateKeyId || !$this->privateKeyPath) {
            throw new \Exception('privateKeyId and privateKeyPath are required.');
        }

        $jwtHeader = [
            'alg' => self::ALG_ES256,
            'kid' => $this->privateKeyId,
            'typ' => 'JWT',
        ];
        $now = time();
        $jwtPayload = [
            'iss' => $this->issuerId,
            'iat' => $now,
            'exp' => $now + 600,
            'aud' => "appstoreconnect-v1",
            'nonce' => uniqid(),
            'bid' => $this->appBundleId,
        ];
        $privateKey = file_get_contents($this->privateKeyPath);
        return JWT::encode($jwtPayload, $privateKey, self::ALG_ES256, $this->privateKeyId, $jwtHeader);
    }

    private function getAppStoreReceiptsApiUrl(string $path): string
    {
        $base = $this->isSandbox ? self::APP_STORE_RECEIPTS_API_SANDBOX_URL : self::APP_STORE_RECEIPTS_API_PRODUCTION_URL;
        return StringUtil::concat('/', $base, $path);
    }

    private function getAppStoreServerApiUrl(string $path): string
    {
        $base = $this->isSandbox ? self::APP_STORE_SERVER_API_SANDBOX_URL : self::APP_STORE_SERVER_API_PRODUCTION_URL;
        return StringUtil::concat('/', $base, $path);
    }

    private function getAuthorizationHeader(): array
    {
        return ['Authorization' => "Bearer {$this->createJwt()}"];
    }

    private function generateRequestIdentifier(): string
    {
        return uniqid();
    }
}
