<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Payjp\Card;
use Payjp\Charge;
use Payjp\Collection;
use Payjp\Customer;
use Payjp\Error\Base as PayJpErrorBase;
use Payjp\Payjp;
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
     * @param string|int $userId
     * @return Customer
     * @throws InvalidApiResponseFormatException
     * @throws PayJpErrorBase
     */
    public function getOrCreateCustomer($userId): Customer
    {
        if ($customer = $this->getCustomerByUserId($userId)) {
            return $customer;
        }
        return $this->createCustomer($userId);
    }

    /**
     * @param string|int $userId
     * @return Customer|null
     * @throws InvalidApiResponseFormatException
     * @throws PayJpErrorBase
     */
    public function getCustomerByUserId($userId): ?Customer
    {
        try {
            return Customer::retrieve($userId);
        } catch (PayJpErrorBase $e) {
            $error = PayJpError::createFromThrownError($e);
            if ($error->is(PayJpError::INVALID_ID)) {
                return null;
            }
            throw $e; // 対象が存在しないエラーではない場合、異常なので投げ直す
        }
    }

    /**
     * @param string|int $userId
     * @return Customer
     * @throws PayJpErrorBase
     */
    private function createCustomer($userId): Customer
    {
        try {
            return Customer::create(['id' => $userId]);
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
}