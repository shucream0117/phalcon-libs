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

    /*
     * Acknowledgement State
     * @see https://developers.google.com/android-publisher/api-ref/rest/v3/purchases.subscriptionsv2#acknowledgementstate
     */
    const ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED = 'ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED';
    const ACKNOWLEDGEMENT_STATE_PENDING = 'ACKNOWLEDGEMENT_STATE_PENDING';
    const ACKNOWLEDGEMENT_STATE_UNSPECIFIED = 'ACKNOWLEDGEMENT_STATE_UNSPECIFIED';

    /*
     * Subscription State
     * @see https://developers.google.com/android-publisher/api-ref/rest/v3/purchases.subscriptionsv2#subscriptionstate
     */
    const SUBSCRIPTION_STATE_UNSPECIFIED = 'SUBSCRIPTION_STATE_UNSPECIFIED';
    const SUBSCRIPTION_STATE_PENDING = 'SUBSCRIPTION_STATE_PENDING';
    const SUBSCRIPTION_STATE_ACTIVE = 'SUBSCRIPTION_STATE_ACTIVE';
    const SUBSCRIPTION_STATE_PAUSED = 'SUBSCRIPTION_STATE_PAUSED';
    const SUBSCRIPTION_STATE_IN_GRACE_PERIOD = 'SUBSCRIPTION_STATE_IN_GRACE_PERIOD';
    const SUBSCRIPTION_STATE_ON_HOLD = 'SUBSCRIPTION_STATE_ON_HOLD';
    const SUBSCRIPTION_STATE_CANCELED = 'SUBSCRIPTION_STATE_CANCELED';
    const SUBSCRIPTION_STATE_EXPIRED = 'SUBSCRIPTION_STATE_EXPIRED';

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
