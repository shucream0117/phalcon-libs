<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Constants;

class TwitterErrorCode
{
    /*
     * エラーコードたち
     * @see https://developer.twitter.com/en/support/twitter-api/error-troubleshooting
     */
    public const ERROR_CODE_RATE_LIMIT_EXCEEDED = 88;
    public const ERROR_CODE_INVALID_OR_EXPIRED_TOKEN = 89;
    public const ERROR_CODE_UNABLE_TO_VERIFY_CREDENTIALS = 99;
    public const ERROR_CODE_OVER_CAPACITY = 130;
    public const ERROR_CODE_INTERNAL_ERROR = 131;
}
