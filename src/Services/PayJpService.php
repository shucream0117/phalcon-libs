<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Payjp\Card;
use Payjp\Charge;
use Payjp\Collection;
use Payjp\Customer;
use Payjp\Error\Base as PayJpErrorBase;
use Payjp\Payjp;
use Payjp\Plan;
use Payjp\Subscription;
use Payjp\ThreeDSecureRequest;
use Payjp\Token;
use Shucream0117\PhalconLib\Entities\PayJp\Error as PayJpError;
use Shucream0117\PhalconLib\Exceptions\InvalidApiResponseFormatException;
use Shucream0117\PhalconLib\Exceptions\SameCreditCardAlreadyRegisteredException;

class PayJpService extends AbstractService
{
    const CURRENCY_JPY = 'jpy';

    /*
     * クレジットカード種別
     */
    const BRAND_VISA = 'Visa';
    const BRAND_MASTER_CARD = 'MasterCard';
    const BRAND_JCB = 'JCB';
    const BRAND_AMERICAN_EXPRESS = 'American Express';
    const BRAND_DINERS_CLUB = 'Diners Club';
    const BRAND_DISCOVER = 'Discover';

    /*
     * ウェブフックイベント
     */
    const WEBHOOK_EVENT_TYPE_CHARGE_SUCCEEDED = 'charge.succeeded';
    const WEBHOOK_EVENT_TYPE_CHARGE_FAILED = 'charge.failed';
    const WEBHOOK_EVENT_TYPE_CHARGE_UPDATED = 'charge.updated';
    const WEBHOOK_EVENT_TYPE_CHARGE_REFUNDED = 'charge.refunded';
    const WEBHOOK_EVENT_TYPE_CHARGE_CAPTURED = 'charge.captured';
    const WEBHOOK_EVENT_TYPE_TOKEN_CREATED = 'token.created';
    const WEBHOOK_EVENT_TYPE_CUSTOMER_CREATED = 'customer.created';
    const WEBHOOK_EVENT_TYPE_CUSTOMER_UPDATED = 'customer.updated';
    const WEBHOOK_EVENT_TYPE_CUSTOMER_DELETED = 'customer.deleted';
    const WEBHOOK_EVENT_TYPE_CUSTOMER_CARD_CREATED = 'customer.card.created';
    const WEBHOOK_EVENT_TYPE_CUSTOMER_CARD_UPDATED = 'customer.card.updated';
    const WEBHOOK_EVENT_TYPE_CUSTOMER_CARD_DELETED = 'customer.card.deleted';
    const WEBHOOK_EVENT_TYPE_PLAN_CREATED = 'plan.created';
    const WEBHOOK_EVENT_TYPE_PLAN_UPDATED = 'plan.updated';
    const WEBHOOK_EVENT_TYPE_PLAN_DELETED = 'plan.deleted';
    const WEBHOOK_EVENT_TYPE_SUBSCRIPTION_CREATED = 'subscription.created';
    const WEBHOOK_EVENT_TYPE_SUBSCRIPTION_UPDATED = 'subscription.updated';
    const WEBHOOK_EVENT_TYPE_SUBSCRIPTION_DELETED = 'subscription.deleted';
    const WEBHOOK_EVENT_TYPE_SUBSCRIPTION_PAUSED = 'subscription.paused';
    const WEBHOOK_EVENT_TYPE_SUBSCRIPTION_RESUMED = 'subscription.resumed';
    const WEBHOOK_EVENT_TYPE_SUBSCRIPTION_CANCELED = 'subscription.canceled';
    const WEBHOOK_EVENT_TYPE_SUBSCRIPTION_RENEWED = 'subscription.renewed';
    const WEBHOOK_EVENT_TYPE_TRANSFER_SUCCEEDED = 'transfer.succeeded';
    const WEBHOOK_EVENT_TYPE_TENANT_UPDATED = 'tenant.updated';

    /*
     * サブスクリプションステータス
     */
    const SUBSCRIPTION_STATUS_ACTIVE = 'active';
    const SUBSCRIPTION_STATUS_PAUSED = 'paused';
    const SUBSCRIPTION_STATUS_CANCELED = 'canceled';
    const SUBSCRIPTION_STATUS_TRIAL = 'trial';


    /*
     * 3Dセキュア認証ステータス
     */
    const THREE_D_SECURE_STATUS_VERIFIED = 'verified'; // 成功
    const THREE_D_SECURE_STATUS_UNVERIFIED = 'unverified'; // 未認証
    const THREE_D_SECURE_STATUS_ATTEMPTED = 'attempted'; // アテンプト
    const THREE_D_SECURE_STATUS_FAILED = 'failed'; // 失敗
    const THREE_D_SECURE_STATUS_ERROR = 'error'; // エラー

    /*
     * エラー
     * @see https://pay.jp/docs/api/#%E3%83%AC%E3%82%B9%E3%83%9D%E3%83%B3%E3%82%B9
     */
    // エラー type
    const ERROR_TYPE_CLIENT = 'client_error'; // リクエストエラー
    const ERROR_TYPE_CARD = 'card_error'; // カードエラー
    const ERROR_TYPE_SERVER = 'server_error'; // サーバーエラー
    const ERROR_TYPE_NOT_ALLOWED_METHOD = 'not_allowed_method_error'; // 許可されていないメソッドエラー
    const ERROR_TYPE_AUTH = 'auth_error'; // 認証エラー
    const ERROR_TYPE_INVALID_REQUEST = 'invalid_request_error'; // 無効なリクエスト

    // エラー code (カード関連)
    const ERROR_CODE_EXPIRED_CARD = 'expired_card'; // カードの有効期限切れ
    const ERROR_CODE_CARD_DECLINED = 'card_declined'; // カードが拒否された
    const ERROR_CODE_FLAGGED = 'card_flagged'; // カードを原因としたエラーが続いたことによる一時的なロックアウト
    const ERROR_CODE_INVALID_CARD = 'invalid_card'; // 無効なカード
    const ERROR_CODE_PROCESSING_ERROR = 'processing_error'; // 決済ネットワーク上で生じたエラー
    const ERROR_CODE_INVALID_CVC = 'invalid_cvc'; // CVCが無効
    const ERROR_UNACCEPTABLE_BRAND = 'unacceptable_brand'; // 未対応のカードブランド

    // エラー code (3Dセキュア関連)
    const ERROR_CODE_INVALID_THREE_D_SECURE_STATE = 'invalid_three_d_secure_state'; // 3Dセキュア中に二重に処理されるなど不正な遷移をした
    const ERROR_CODE_THREE_D_SECURE_INCOMPLETE = 'three_d_secure_incompleted'; // 3Dセキュアフローが完了していない状態で別の操作を行った
    const ERROR_CODE_THREE_D_SECURE_FAILED = 'three_d_secure_failed'; // 3Dセキュア認証に失敗した
    const ERROR_CODE_NOT_IN_THREE_D_SECURE_FLOW  = 'not_in_three_d_secure_flow'; // 3Dセキュア対象外の支払いか、3Dセキュアフローが時間切れになった
    const ERROR_CODE_UNVERIFIED_TOKEN = 'unverified_token'; // 3Dセキュアが完了していないトークンで支払いが行われた
    const ERROR_CODE_THREE_D_SECURE_EXPIRED = 'three_d_secure_expired'; // 3Dセキュアの認証完了期限が切れている


    public function __construct(string $apiKey)
    {
        Payjp::setApiKey($apiKey);
    }

    /**
     * IDでCustomerを取得
     *
     * @param string $id
     * @return Customer|null
     * @throws InvalidApiResponseFormatException
     * @throws PayJpErrorBase
     */
    public function getCustomerById(string $id): ?Customer
    {
        try {
            return Customer::retrieve($id);
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->getErrorCode() === PayJpError::INVALID_ID) {
                return null;
            }
            throw $e; // 対象が存在しないエラーではない場合、異常なので投げ直す
        }
    }

    /**
     * Customerを作成
     *
     * @param string|null $id 指定しない場合はPAY.JP側で自動的に振られる
     * @param string|null $email
     * @param string|null $description
     * @param string|null $cardToken
     * @param array $metadata
     * @return Customer
     * @throws PayJpErrorBase
     */
    public function createCustomer(
        ?string $id = null,
        ?string $email = null,
        ?string $description = null,
        ?string $cardToken = null,
        array $metadata = []
    ): Customer {
        $params = ['metadata' => $metadata];
        if (!is_null($id)) {
            $params['id'] = $id;
        }
        if (!is_null($email)) {
            $params['email'] = $email;
        }
        if (!is_null($description)) {
            $params['description'] = $description;
        }
        if (!is_null($cardToken)) {
            $params['card'] = $cardToken;
        }
        try {
            return Customer::create($params);
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * カードトークンをユーザに紐付ける
     *
     * @param Customer $customer
     * @param string $token
     * @param bool $default
     * @return Card
     * @throws InvalidApiResponseFormatException
     * @throws PayJpErrorBase
     * @throws SameCreditCardAlreadyRegisteredException
     */
    public function registerCreditCardToken(Customer $customer, string $token, bool $default): Card
    {
        try {
            /** @var Collection $cards */
            $cards = $customer->offsetGet('cards');
            /** @var Card $card */
            $card = $cards->create(['card' => $token, 'default' => $default]);
            return $card;
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->getErrorCode() === PayJpError::ALREADY_HAVE_THE_SAME_CARD) {
                throw new SameCreditCardAlreadyRegisteredException();
            }
            throw $e;
        }
    }

    /**
     * クレカ削除
     *
     * @param Card $card
     * @throws PayJpErrorBase
     */
    public function unregisterCreditCard(Card $card): void
    {
        try {
            $card->delete();
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 登録してあるクレカ一覧
     *
     * @param Customer $customer
     * @param int $limit
     * @param int $offset
     * @return Card[]
     * @throws PayJpErrorBase
     * @throws InvalidApiResponseFormatException
     */
    public function getCreditCards(Customer $customer, int $limit = 10, int $offset = 0): array
    {
        try {
            /** @var Collection $cards */
            $cards = $customer->offsetGet('cards');
            $cards = $cards->all(['limit' => $limit, 'offset' => $offset]);
            return $cards->offsetGet('data');
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->getErrorCode() === PayJpError::MISSING_CARD) { // 登録されていない場合
                return [];
            }
            throw $e;
        }
    }

    /**
     * @param Customer $customer
     * @param string $cardId
     * @return Card|null
     * @throws PayJpErrorBase
     * @throws InvalidApiResponseFormatException
     */
    public function getCreditCardByCardId(Customer $customer, string $cardId): ?Card
    {
        try {
            /** @var Collection $cards */
            $cards = $customer->offsetGet('cards');
            /** @var Card $card */
            $card = $cards->retrieve($cardId);
            return $card;
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->getErrorCode() === PayJpError::INVALID_ID) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * カード情報を更新
     *
     * @param Card $card
     * @param array<string, mixed> $params
     * @return Card
     */
    public function updateCard(Card $card, array $params): Card
    {
        if (!$params) {
            return $card;
        }
        foreach ($params as $key => $value) {
            $card[$key] = $value;
        }
        $card->save();
        return $card;
    }

    /**
     * 支払いを行う(即確定させる)
     *
     * @param Customer $customer
     * @param Card $card
     * @param int $amount
     * @param array $metadata
     * @return Charge
     * @throws PayJpErrorBase
     */
    public function chargeWithCapture(
        Customer $customer,
        Card $card,
        int $amount,
        bool $threeDSecure = false,
        array $metadata = []
    ): Charge {
        return $this->charge([
            'customer' => $customer->offsetGet('id'),
            'card' => $card->offsetGet('id'),
            'amount' => $amount,
            'currency' => self::CURRENCY_JPY,
            'capture' => true,
            'three_d_secure' => $threeDSecure,
            'metadata' => $metadata,
        ]);
    }

    /**
     * 支払いを行う(カードの認証と支払い額の確保のみ行う)
     *
     * @param Customer $customer
     * @param Card $card
     * @param int $amount
     * @param int $expiryDays
     * @param array $metadata 任意のキーバリューデータ
     * @return Charge
     * @throws PayJpErrorBase
     */
    public function chargeWithoutCapture(
        Customer $customer,
        Card $card,
        int $amount,
        int $expiryDays,
        bool $threeDSecure = false,
        array $metadata = []
    ): Charge {
        return $this->charge([
            'customer' => $customer->offsetGet('id'),
            'card' => $card->offsetGet('id'),
            'amount' => $amount,
            'currency' => self::CURRENCY_JPY,
            'capture' => false,
            'expiry_days' => $expiryDays,
            'three_d_secure' => $threeDSecure,
            'metadata' => $metadata,
        ]);
    }

    /**
     * 決済を行う
     * @param array $params
     * @return Charge
     * @throws PayJpErrorBase
     */
    private function charge(array $params): Charge
    {
        try {
            return Charge::create($params);
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 支払い情報を取得
     *
     * @param string $chargeId
     * @return Charge|null
     * @throws InvalidApiResponseFormatException
     * @throws PayJpErrorBase
     */
    public function getChargeById(string $chargeId): ?Charge
    {
        try {
            return Charge::retrieve($chargeId);
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->getErrorCode() === PayJpError::INVALID_ID) {
                return null;
            }
            throw $e; // 対象が存在しないエラーではない場合、異常なので投げ直す
        }
    }

    /**
     * 支払い情報一覧を取得
     *
     * @param string $customerId
     * @param int $limit
     * @param int $offset
     * @param string|null $subscriptionId
     * @param int|null $sinceTimestamp
     * @param int|null $untilTimestamp
     * @param string|null $tenantId
     * @return Charge[]
     * @throws PayJpErrorBase
     */
    public function getChargeListByCustomerId(
        string $customerId,
        int $limit = 100,
        int $offset = 0,
        ?string $subscriptionId = null,
        ?int $sinceTimestamp = null,
        ?int $untilTimestamp = null,
        ?string $tenantId = null
    ): array {
        try {
            $params = [
                'customer' => $customerId,
                'limit' => $limit,
                'offset' => $offset,
            ];
            if (!is_null($subscriptionId)) {
                $params['subscription'] = $subscriptionId;
            }
            if (!is_null($sinceTimestamp)) {
                $params['since'] = $sinceTimestamp;
            }
            if (!is_null($untilTimestamp)) {
                $params['until'] = $untilTimestamp;
            }
            if (!is_null($tenantId)) {
                $params['tenant'] = $tenantId;
            }
            /** @var Collection $list */
            $list = Charge::all($params);
            return $list['data'];
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 支払いを確定する
     *
     * @param Charge $charge
     * @return Charge
     * @throws PayJpErrorBase
     */
    public function capture(Charge $charge): Charge
    {
        try {
            return $charge->capture();
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 返金する
     *
     * @param Charge $charge
     * @param int|null $amount
     * @param string|null $reason
     * @return Charge
     * @throws PayJpErrorBase
     */
    public function refund(Charge $charge, ?int $amount = null, ?string $reason = null): Charge
    {
        $params = [];
        if (!is_null($amount)) {
            $params['amount'] = $amount;
        }
        if (!is_null($reason)) {
            $params['refund_reason'] = $reason;
        }
        try {
            return $charge->refund($params ?: null);
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 月額プランを作成
     *
     * @param int $price
     * @param int|null $billingDay 課金日(1〜31) 月によって存在しない日は自動的に月末で処理されるので、31を指定すれば常に月末が課金日になる
     * @param string|null $name
     * @param int|null $trialDays
     * @param array $metadata // 任意のキーバリューデータ
     * @return Plan
     * @throws PayJpErrorBase
     */
    public function createMonthlyPlan(
        int $price,
        ?int $billingDay,
        ?string $name = null,
        ?int $trialDays = null,
        array $metadata = []
    ): Plan {
        return $this->createPlan('month', $price, $billingDay, $name, $trialDays, $metadata);
    }

    /**
     * 年間プランを作成
     *
     * @param int $price
     * @param string|null $name
     * @param int|null $trialDays
     * @param array $metadata // 任意のキーバリューデータ
     * @return Plan
     * @throws PayJpErrorBase
     */
    public function createYearlyPlan(
        int $price,
        ?string $name = null,
        ?int $trialDays = null,
        array $metadata = []
    ): Plan {
        return $this->createPlan('year', $price, null, $name, $trialDays, $metadata);
    }

    /**
     * @param string $interval
     * @param int $price
     * @param int|null $billingDay 課金日(1〜31) 月によって存在しない日は自動的に月末で処理されるので、31を指定すれば常に月末が課金日になる
     * @param string|null $name
     * @param int|null $trialDays
     * @param array $metadata // 任意のキーバリューデータ
     * @return Plan
     * @throws PayJpErrorBase
     */
    private function createPlan(
        string $interval,
        int $price,
        ?int $billingDay,
        ?string $name = null,
        ?int $trialDays = null,
        array $metadata = []
    ): Plan {
        $params = [
            'interval' => $interval,
            'amount' => $price,
            'currency' => self::CURRENCY_JPY,
            'metadata' => $metadata,
        ];
        if (!is_null($billingDay)) {
            if ($billingDay < 1 || 31 < $billingDay) {
                throw new \InvalidArgumentException('billingDay should be between 1 and 31');
            }
            $params['billing_day'] = $billingDay;
        }

        if (!is_null($trialDays) && (1 <= $trialDays)) {
            $params['trial_days'] = $trialDays;
        }
        if (!is_null($name)) {
            $params['name'] = $name;
        }

        try {
            return Plan::create($params);
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * Planを取得
     *
     * @param string $planId
     * @return Plan|null
     * @throws PayJpErrorBase
     */
    public function getPlanById(string $planId): ?Plan
    {
        try {
            return Plan::retrieve($planId);
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->getErrorCode() === PayJpError::INVALID_ID) {
                return null;
            }
            throw $e; // 対象が存在しないエラーではない場合、異常なので投げ直す
        }
    }

    /**
     * 定期課金情報を取得
     *
     * @param string $subscriptionId
     * @return Subscription
     * @throws PayJpErrorBase
     */
    public function getSubscriptionById(string $subscriptionId): ?Subscription
    {
        try {
            return Subscription::retrieve($subscriptionId);
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->getErrorCode() === PayJpError::INVALID_ID) {
                return null;
            }
            throw $e; // 対象が存在しないエラーではない場合、異常なので投げ直す
        }
    }

    /**
     * @param string $customerId
     * @param int $limit
     * @param int $offset
     * @param int|null $sinceTimestamp
     * @param int|null $untilTimestamp
     * @param string|null $planId
     * @param string|null $status
     * @return Collection
     */
    public function getSubscriptionsByCustomerId(
        string $customerId,
        int $limit = 100,
        int $offset = 0,
        ?int $sinceTimestamp = null,
        ?int $untilTimestamp = null,
        ?string $planId = null,
        ?string $status = null
    ): Collection {
        $params = [
            'customer' => $customerId,
            'limit' => $limit,
            'offset' => $offset,
        ];
        if (!is_null($sinceTimestamp)) {
            $params['since'] = $sinceTimestamp;
        }
        if (!is_null($untilTimestamp)) {
            $params['until'] = $untilTimestamp;
        }
        if (!is_null($planId)) {
            $params['plan'] = $planId;
        }
        if (!is_null($status)) {
            $params['status'] = $status;
        }
        /** @var Collection $result */
        $result = Subscription::all($params);
        return $result;
    }

    /**
     * PlanID指定で定期課金リストを取得する
     * @param string|null $planId
     * @param int $limit
     * @param int $offset
     * @param int|null $sinceTimestamp
     * @param int|null $untilTimestamp
     * @param string|null $status
     * @return Collection
     */
    public function getSubscriptionsByPlanId(
        string $planId,
        int $limit = 100,
        int $offset = 0,
        ?int $sinceTimestamp = null,
        ?int $untilTimestamp = null,
        ?string $status = null
    ): Collection {
        $params = [
            'plan' => $planId,
            'limit' => $limit,
            'offset' => $offset,
        ];
        if (!is_null($sinceTimestamp)) {
            $params['since'] = $sinceTimestamp;
        }
        if (!is_null($untilTimestamp)) {
            $params['until'] = $untilTimestamp;
        }
        if (!is_null($planId)) {
            $params['plan'] = $planId;
        }
        if (!is_null($status)) {
            $params['status'] = $status;
        }
        /** @var Collection $result */
        $result = Subscription::all($params);
        return $result;
    }

    /**
     * 定期課金を作成
     *
     * @param Customer $customer
     * @param Plan $plan
     * @param int|null $trialEndTimestamp 無料期間をいつ終わらせるかのタイムスタンプ。即時課金するにはnull
     * @param bool $prorate 日割りするかどうか
     * @param array $metadata
     * @return Subscription
     * @throws PayJpErrorBase
     */
    public function createSubscription(
        Customer $customer,
        Plan $plan,
        ?int $trialEndTimestamp,
        bool $prorate,
        array $metadata = []
    ): Subscription {
        try {
            return Subscription::create([
                'customer' => $customer['id'],
                'plan' => $plan['id'],
                'prorate' => $prorate,
                'metadata' => $metadata,
                'trial_end' => $trialEndTimestamp ?: 'now',
            ]);
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 定期課金を一時停止
     *
     * @param Subscription $subscription
     * @return Subscription
     * @throws PayJpErrorBase
     */
    public function pauseSubscription(Subscription $subscription): Subscription
    {
        try {
            return $subscription->pause();
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 定期課金を再開
     *
     * @param Subscription $subscription
     * @param int|null $trialEndTimestamp
     * @param bool $prorate
     * @return Subscription
     * @throws PayJpErrorBase
     */
    public function resumeSubscription(
        Subscription $subscription,
        ?int $trialEndTimestamp,
        bool $prorate
    ): Subscription {
        try {
            return $subscription->resume([
                'prorate' => $prorate,
                'trial_end' => $trialEndTimestamp ?: 'now',
            ]);
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 定期課金をキャンセル(現在の周期の終了日をもって定期課金を終了させる)
     *
     * @param Subscription $subscription
     * @return Subscription
     * @throws PayJpErrorBase
     */
    public function cancelSubscription(Subscription $subscription): Subscription
    {
        try {
            return $subscription->cancel();
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 終了日を待たずに直ちに定期課金を削除する。
     *
     * @param Subscription $subscription
     * @param bool $prorate 日割りで残額返金するかどうか
     * @return Subscription
     * @throws PayJpErrorBase
     */
    public function forceDeleteSubscription(Subscription $subscription, bool $prorate): Subscription
    {
        try {
            return $subscription->delete([
                'prorate' => $prorate,
            ]);
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * プランを更新
     * @see https://payjp.hatenablog.com/entry/2016/01/19/080000
     *
     * @param Subscription $subscription
     * @param string $newPlanId
     * @param int|null $trialEndTimestamp
     * @param bool $prorate
     * @param array $metadata
     * @return Subscription
     * @throws PayJpErrorBase
     */
    public function changePlan(
        Subscription $subscription,
        string $newPlanId,
        ?int $trialEndTimestamp = null,
        bool $prorate = false,
        array $metadata = []
    ): Subscription {
        try {
            $subscription['plan'] = $newPlanId;
            $subscription['trial_end'] = $trialEndTimestamp ?: 'now';
            $subscription['prorate'] = $prorate;
            if ($metadata) {
                $subscription['metadata'] = $metadata;
            }
            return $subscription->save();
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 次回更新プランの設定を変更する
     * @param Subscription $subscription
     * @param string $nextPlanId
     * @return Subscription
     * @throws PayJpErrorBase
     */
    public function changeNextPlan(Subscription $subscription, string $nextPlanId): Subscription
    {
        try {
            $subscription['next_cycle_plan'] = $nextPlanId;
            return $subscription->save();
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * Tokenオブジェクトを取得
     *
     * @param string $id
     * @return Token|null
     * @throws PayJpErrorBase
     */
    public function getTokenById(string $id): ?Token
    {
        try {
            return Token::retrieve($id);
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->getErrorCode() === PayJpError::INVALID_ID) {
                return null;
            }
            throw $e; // 対象が存在しないエラーではない場合、異常なので投げ直す
        }
    }

    /**
     * 3Dセキュアの完了処理(Token)
     *
     * @param Token $token
     * @return Token
     */
    public function completeThreeDSecureByToken(Token $token): Token
    {
        return $token->tdsFinish();
    }

    /**
     * 3Dセキュアの完了処理(Charge)
     *
     * @param Charge $charge
     * @return Charge
     */
    public function completeThreeDSecureByCharge(Charge $charge): Charge
    {
        return $charge->tdsFinish();
    }

    /**
     * カードを指定して3Dセキュアリクエストを作成
     *
     * @param Card $card
     * @return ThreeDSecureRequest
     * @see https://pay.jp/docs/api/#3d%E3%82%BB%E3%82%AD%E3%83%A5%E3%82%A2%E3%83%AA%E3%82%AF%E3%82%A8%E3%82%B9%E3%83%88%E3%82%92%E4%BD%9C%E6%88%90
     */
    public function createThreeDSecureRequest(Card $card): ThreeDSecureRequest
    {
        return ThreeDSecureRequest::create([
            'resource_id' => $card['id'],
        ]);
    }

    /**
     * 3Dセキュアリクエストを取得
     *
     * @param string $id 'tdsr_xxxxx' 形式
     * @return ThreeDSecureRequest|null
     * @throws PayJpErrorBase
     */
    public function getThreeDSecureRequestById(string $id): ?ThreeDSecureRequest
    {
        try {
            return ThreeDSecureRequest::retrieve($id);
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->getErrorCode() === PayJpError::INVALID_ID) {
                return null;
            }
            throw $e; // 対象が存在しないエラーではない場合、異常なので投げ直す
        }
    }

    /**
     * 3Dセキュアリクエストのリストを取得する
     * @param int $limit
     * @param int $offset
     * @param int|null $sinceTimestamp
     * @param int|null $untilTimestamp
     * @param string|null $resourceId カードIDやChargeIDなど
     * @param string|null $tenantId
     * @return Collection
     */
    public function getThreeDSecureRequestList(
        int $limit = 100,
        int $offset = 0,
        ?int $sinceTimestamp = null,
        ?int $untilTimestamp = null,
        ?string $resourceId = null,
        ?string $tenantId = null
    ): Collection {
        $params = [
            'limit' => $limit,
            'offset' => $offset,
        ];
        if (!is_null($sinceTimestamp)) {
            $params['since'] = $sinceTimestamp;
        }
        if (!is_null($untilTimestamp)) {
            $params['until'] = $untilTimestamp;
        }
        if (!is_null($resourceId)) {
            $params['resource_id'] = $resourceId;
        }
        if (!is_null($tenantId)) {
            $params['tenant_id'] = $tenantId;
        }
        /** @var Collection $result */
        $result = ThreeDSecureRequest::all($params);
        return $result;
    }
}
