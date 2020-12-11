<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\PayJp;

use Payjp\Error\Base as PayJpErrorBase;
use Shucream0117\PhalconLib\Exceptions\InvalidApiResponseFormatException;

class Error
{
    // types
    const TYPE_CLIENT_ERROR = 'client_error';
    const TYPE_CARD_ERROR = 'card_error';
    const TYPE_SERVER_ERROR = 'server_error';
    const TYPE_NOT_FOUND_ERROR = 'not_found_error';
    const TYPE_NOT_ALLOWED_METHOD_ERROR = 'not_allowed_method_error';
    const TYPE_AUTH_ERROR = 'auth_error';
    const TYPE_INVALID_REQUEST_ERROR = 'invalid_request_error';

    // codes
    const INVALID_NUMBER = 'invalid_number';
    const INVALID_CVC = 'invalid_cvc';
    const INVALID_EXPIRATION_DATE = 'invalid_expiration_date';
    const INVALID_EXPIRY_MONTH = 'invalid_expiry_month';
    const INVALID_EXPIRY_YEAR = 'invalid_expiry_year';
    const EXPIRED_CARD = 'expired_card';
    const CARD_DECLINED = 'card_declined';
    const PROCESSING_ERROR = 'processing_error';
    const MISSING_CARD = 'missing_card';
    const UNACCEPTABLE_BRAND = 'unacceptable_brand';
    const INVALID_ID = 'invalid_id';
    const NO_API_KEY = 'no_api_key';
    const INVALID_API_KEY = 'invalid_api_key';
    const INVALID_PLAN = 'invalid_plan';
    const INVALID_EXPIRY_DAYS = 'invalid_expiry_days';
    const UNNECESSARY_EXPIRY_DAYS = 'unnecessary_expiry_days';
    const INVALID_FLEXIBLE_ID = 'invalid_flexible_id';
    const INVALID_TIMESTAMP = 'invalid_timestamp';
    const INVALID_TRIAL_END = 'invalid_trial_end';
    const INVALID_STRING_LENGTH = 'invalid_string_length';
    const INVALID_COUNTRY = 'invalid_country';
    const INVALID_CURRENCY = 'invalid_currency';
    const INVALID_ADDRESS_ZIP = 'invalid_address_zip';
    const INVALID_AMOUNT = 'invalid_amount';
    const INVALID_PLAN_AMOUNT = 'invalid_plan_amount';
    const INVALID_CARD = 'invalid_card';
    const INVALID_CARD_NAME = 'invalid_card_name';
    const INVALID_CARD_COUNTRY = 'invalid_card_country';
    const INVALID_CARD_ADDRESS_ZIP = 'invalid_card_address_zip';
    const INVALID_CARD_ADDRESS_STATE = 'invalid_card_address_state';
    const INVALID_CARD_ADDRESS_CITY = 'invalid_card_address_city';
    const INVALID_CARD_ADDRESS_LINE = 'invalid_card_address_line';
    const INVALID_CUSTOMER = 'invalid_customer';
    const INVALID_BOOLEAN = 'invalid_boolean';
    const INVALID_EMAIL = 'invalid_email';
    const NO_ALLOWED_PARAM = 'no_allowed_param';
    const NO_PARAM = 'no_param';
    const INVALID_QUERYSTRING = 'invalid_querystring';
    const MISSING_PARAM = 'missing_param';
    const INVALID_PARAM_KEY = 'invalid_param_key';
    const NO_PAYMENT_METHOD = 'no_payment_method';
    const PAYMENT_METHOD_DUPLICATE = 'payment_method_duplicate';
    const PAYMENT_METHOD_DUPLICATE_INCLUDING_CUSTOMER = 'payment_method_duplicate_including_customer';
    const FAILED_PAYMENT = 'failed_payment';
    const INVALID_REFUND_AMOUNT = 'invalid_refund_amount';
    const ALREADY_REFUNDED = 'already_refunded';
    const INVALID_AMOUNT_TO_NOT_CAPTURED = 'invalid_amount_to_not_captured';
    const REFUND_AMOUNT_GT_NET = 'refund_amount_gt_net';
    const CAPTURE_AMOUNT_GT_NET = 'capture_amount_gt_net';
    const INVALID_REFUND_REASON = 'invalid_refund_reason';
    const ALREADY_CAPTURED = 'already_captured';
    const CANT_CAPTURE_REFUNDED_CHARGE = 'cant_capture_refunded_charge';
    const CANT_REAUTH_REFUNDED_CHARGE = 'cant_reauth_refunded_charge';
    const CHARGE_EXPIRED = 'charge_expired';
    const ALREADY_EXIST_ID = 'already_exist_id';
    const TOKEN_ALREADY_USED = 'token_already_used';
    const ALREADY_HAVE_CARD = 'already_have_card';
    const DONT_HAS_THIS_CARD = 'dont_has_this_card';
    const DOESNT_HAVE_CARD = 'doesnt_have_card';
    const ALREADY_HAVE_THE_SAME_CARD = 'already_have_the_same_card';
    const INVALID_INTERVAL = 'invalid_interval';
    const INVALID_TRIAL_DAYS = 'invalid_trial_days';
    const INVALID_BILLING_DAY = 'invalid_billing_day';
    const BILLING_DAY_FOR_NON_MONTHLY_PLAN = 'billing_day_for_non_monthly_plan';
    const EXIST_SUBSCRIBERS = 'exist_subscribers';
    const ALREADY_SUBSCRIBED = 'already_subscribed';
    const ALREADY_CANCELED = 'already_canceled';
    const ALREADY_PAUSED = 'already_paused';
    const SUBSCRIPTION_WORKED = 'subscription_worked';
    const CANNOT_CHANGE_PRORATE_STATUS = 'cannot_change_prorate_status';
    const TOO_MANY_METADATA_KEYS = 'too_many_metadata_keys';
    const INVALID_METADATA_KEY = 'invalid_metadata_key';
    const INVALID_METADATA_VALUE = 'invalid_metadata_value';
    const APPLE_PAY_DISABLED_IN_LIVEMODE = 'apple_pay_disabled_in_livemode';
    const INVALID_APPLE_PAY_TOKEN = 'invalid_apple_pay_token';
    const TEST_CARD_ON_LIVEMODE = 'test_card_on_livemode';
    const NOT_ACTIVATED_ACCOUNT = 'not_activated_account';
    const TOO_MANY_TEST_REQUEST = 'too_many_test_request';
    const PAYJP_WRONG = 'payjp_wrong';
    const PG_WRONG = 'pg_wrong';
    const NOT_FOUND = 'not_found';
    const NOT_ALLOWED_METHOD = 'not_allowed_method';
    const OVER_CAPACITY = 'over_capacity';
    const REFUND_LIMIT_EXCEEDED = 'refund_limit_exceeded';

    private string $errorType;
    private string $errorCode;
    private int $statusCode;

    private function __construct(int $statusCode, string $errorType, string $errorCode)
    {
        $this->statusCode = $statusCode;
        $this->errorType = $errorType;
        $this->errorCode = $errorCode;
    }

    public function getErrorType(): string
    {
        return $this->errorType;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @param string $errorCode
     * @return bool
     * @deprecated
     * Use getErrorCode() instead.
     */
    public function is(string $errorCode): bool
    {
        return $this->errorCode === $errorCode;
    }

    /**
     * 例外からエラーエンティティを作成
     *
     * @param PayJpErrorBase $error
     * @return self
     * @throws InvalidApiResponseFormatException
     */
    public static function createFromThrownError(PayJpErrorBase $error): self
    {
        $jsonBodyArray = $error->getJsonBody();
        /** @var string|null $errorType */
        $errorType = $jsonBodyArray['error']['type'] ?? null;
        /** @var string|null $errorCode */
        $errorCode = $jsonBodyArray['error']['code'] ?? null;
        /** @var int|null $statusCode */
        $statusCode = $jsonBodyArray['error']['status'] ?? null;

        if (!$errorCode || !$errorType || !$statusCode) {
            throw new InvalidApiResponseFormatException('invalid format error response was returned from PAY.JP');
        }
        return new static($statusCode, $errorType, $errorCode);
    }
}
