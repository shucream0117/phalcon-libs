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
use Shucream0117\PhalconLib\Entities\PayJp\Error as PayJpError;
use Shucream0117\PhalconLib\Exceptions\InvalidApiResponseFormatException;
use Shucream0117\PhalconLib\Exceptions\SameCreditCardAlreadyRegisteredException;

class PayJpService extends AbstractService
{
    const CURRENCY_JPY = 'jpy';

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
            if ($error->is(PayJpError::INVALID_ID)) {
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
            if ($error->is(PayJpError::SAME_CARD)) {
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
            if ($error->is(PayJpError::MISSING_CARD)) { // 登録されていない場合
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
            if ($error->is(PayJpError::INVALID_ID)) {
                return null;
            }
            throw $e;
        }
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
    public function chargeWithCapture(Customer $customer, Card $card, int $amount, array $metadata = []): Charge
    {
        return $this->charge([
            'customer' => $customer->offsetGet('id'),
            'card' => $card->offsetGet('id'),
            'amount' => $amount,
            'currency' => self::CURRENCY_JPY,
            'capture' => true,
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
        array $metadata = []
    ): Charge {
        return $this->charge([
            'customer' => $customer->offsetGet('id'),
            'card' => $card->offsetGet('id'),
            'amount' => $amount,
            'currency' => self::CURRENCY_JPY,
            'capture' => false,
            'expiry_days' => $expiryDays,
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
            if ($error->is(PayJpError::INVALID_ID)) {
                return null;
            }
            throw $e; // 対象が存在しないエラーではない場合、異常なので投げ直す
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
     * @return Charge
     * @throws PayJpErrorBase
     */
    public function refund(Charge $charge): Charge
    {
        try {
            return $charge->refund();
        } catch (PayJpErrorBase $e) {
            throw $e;
        }
    }

    /**
     * 月額プランを作成
     *
     * @param int $price
     * @param int $billingDay 課金日(1〜31) 月によって存在しない日は自動的に月末で処理されるので、31を指定すれば常に月末が課金日になる
     * @param string|null $name
     * @param int|null $trialDays
     * @param array $metadata // 任意のキーバリューデータ
     * @return Plan
     * @throws PayJpErrorBase
     */
    public function createMonthlyPlan(
        int $price,
        int $billingDay,
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
        if ($name) {
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
            if ($error->is(PayJpError::INVALID_ID)) {
                return null;
            }
            throw $e; // 対象が存在しないエラーではない場合、異常なので投げ直す
        }
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
}
