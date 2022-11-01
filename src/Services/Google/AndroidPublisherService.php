<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\Google;

use Google\Service\AndroidPublisher\SubscriptionPurchaseV2;
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
     * 定期購読の詳細を取得
     * @param string $packageName
     * @param string $productId
     * @param string $purchaseToken
     * @param array $optionalParams
     * @return \Google_Service_AndroidPublisher_SubscriptionPurchase
     *
     * @deprecated use getSubscriptionPurchaseV2
     */
    public function getSubscriptionPurchase(
        string $packageName,
        string $productId,
        string $purchaseToken,
        array $optionalParams = []
    ): \Google_Service_AndroidPublisher_SubscriptionPurchase {
        return $this->googleServiceAndroidPublisher->purchases_subscriptions->get(
            $packageName,
            $productId,
            $purchaseToken,
            $optionalParams
        );
    }

    /**
     * 定期購読の詳細を取得
     * @see https://developers.google.com/android-publisher/api-ref/rest/v3/purchases.subscriptionsv2/get
     *
     * @param string $packageName
     * @param string $purchaseToken
     * @param array $optionalParams
     * @return SubscriptionPurchaseV2
     */
    public function getSubscriptionPurchaseV2(
        string $packageName,
        string $purchaseToken,
        array $optionalParams = []
    ): SubscriptionPurchaseV2 {
        return $this->googleServiceAndroidPublisher->purchases_subscriptionsv2->get(
            $packageName,
            $purchaseToken,
            $optionalParams
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
    public function verifyReceipt(string $receiptJson, string $signature, string $publicKeyPath): bool
    {
        if (!file_exists($publicKeyPath)) {
            throw new \InvalidArgumentException("invalid key path");
        }
        $publicKeyId = openssl_get_publickey(file_get_contents($publicKeyPath));
        return openssl_verify($receiptJson, $signature, $publicKeyId) === 1;
    }

    /**
     * 定期課金のキャンセル(キャンセルしても期限内は有効)
     * @param string $packageName
     * @param string $productId
     * @param string $purchaseToken
     * @param array $optionalParams
     */
    public function cancelSubscription(
        string $packageName,
        string $productId,
        string $purchaseToken,
        array $optionalParams = []
    ): void {
        $this->googleServiceAndroidPublisher->purchases_subscriptions->cancel(
            $packageName,
            $productId,
            $purchaseToken,
            $optionalParams
        );
    }
}
