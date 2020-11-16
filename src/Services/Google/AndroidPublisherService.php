<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\Google;

use Google_Service_AndroidPublisher;
use Shucream0117\PhalconLib\Services\AbstractService;

class AndroidPublisherService extends AbstractService
{
    private Google_Service_AndroidPublisher $googleServiceAndroidPublisher;

    const SCOPE_ANDROID_PUBLISHER = 'https://www.googleapis.com/auth/androidpublisher';

    public function __construct(Google_Service_AndroidPublisher $googleServiceAndroidPublisher)
    {
        $googleServiceAndroidPublisher->getClient()->addScope(self::SCOPE_ANDROID_PUBLISHER);
        $this->googleServiceAndroidPublisher = $googleServiceAndroidPublisher;
    }

    /**
     * 定期購読の購入を承認する
     *
     * @param string $packageName
     * @param string $subscriptionId
     * @param string $token
     * @param string|null $developerPayload
     */
    public function acknowledgeSubscription(
        string $packageName,
        string $subscriptionId,
        string $token,
        ?string $developerPayload = null
    ): void {
        $requestBody = new \Google_Service_AndroidPublisher_SubscriptionPurchasesAcknowledgeRequest();
        if ($developerPayload) {
            $requestBody->setDeveloperPayload($developerPayload);
        }
        $this->googleServiceAndroidPublisher->purchases_subscriptions->acknowledge(
            $packageName,
            $subscriptionId,
            $token,
            new \Google_Service_AndroidPublisher_SubscriptionPurchasesAcknowledgeRequest()
        );
    }

    /**
     * レシートの検証
     *
     * @param string $receiptJson
     * @param string $signature
     * @param string $publicKeyPath path to public key
     * @return bool
     */
    public function verifyReceipt(string $receiptJson, string $signature, string $publicKeyPath)
    {
        if (!file_exists($publicKeyPath)) {
            throw new \InvalidArgumentException("invalid key path");
        }
        $publicKeyId = openssl_get_publickey(file_get_contents($publicKeyPath));
        return openssl_verify($receiptJson, $signature, $publicKeyId) === 1;
    }
}
