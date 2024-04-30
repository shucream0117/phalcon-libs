<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Constants;

class TwitterErrorCode
{
    /*
     * エラーコードたち
     * @see https://developer.twitter.com/en/support/twitter-api/error-troubleshooting
     */
    public const RATE_LIMIT_EXCEEDED = 88;
    public const INVALID_OR_EXPIRED_TOKEN = 89;
    public const UNABLE_TO_VERIFY_CREDENTIALS = 99;
    public const OVER_CAPACITY = 130;
    public const INTERNAL_ERROR = 131;
}
