<?php

namespace Tests\Unit\Exceptions;

use Shucream0117\PhalconLib\Constants\TwitterErrorCode;
use Shucream0117\PhalconLib\Exceptions\TwitterApiErrorException;
use Tests\Unit\TestBase;

class TwitterApiErrorExceptionTest extends TestBase
{
    /**
     * @covers TwitterApiErrorException::hasUnauthorizedError
     */
    public function testHasUnauthorizedError()
    {
        $e = new TwitterApiErrorException();

        /*
         * v1.1 の場合
         */
        $e->setErrors([
            ['code' => TwitterErrorCode::INVALID_OR_EXPIRED_TOKEN],
        ]);
        $this->assertTrue($e->hasUnauthorizedError());

        $e->setErrors([
            ['code' => TwitterErrorCode::INTERNAL_ERROR],
        ]);
        $this->assertFalse($e->hasUnauthorizedError());

        /*
         * v2 の場合
         */
        $resJson = '{"title":"Unauthorized","type":"about:blank","status":401,"detail":"Unauthorized"}';
        $e->setResponseBody(json_decode($resJson, true));
        $this->assertTrue($e->hasUnauthorizedError());

        $resJson = '{"title":"Too Many Requests","detail":"Too Many Requests","type": "about:blank","status":429}';
        $e->setResponseBody(json_decode($resJson, true));
        $this->assertFalse($e->hasUnauthorizedError());
    }

    /**
     * @covers TwitterApiErrorException::hasTooManyRequestError
     */
    public function testHasTooManyRequestError()
    {
        $e = new TwitterApiErrorException();

        /*
         * v1.1 の場合
         */
        $e->setErrors([
            ['code' => TwitterErrorCode::RATE_LIMIT_EXCEEDED],
        ]);
        $this->assertTrue($e->hasTooManyRequestError());

        $e->setErrors([
            ['code' => TwitterErrorCode::INTERNAL_ERROR],
        ]);
        $this->assertFalse($e->hasTooManyRequestError());

        /*
         * v2 の場合
         */
        $resJson = '{"title":"Unauthorized","type":"about:blank","status":401,"detail":"Unauthorized"}';
        $e->setResponseBody(json_decode($resJson, true));
        $this->assertFalse($e->hasTooManyRequestError());

        $resJson = '{"title":"Too Many Requests","detail":"Too Many Requests","type": "about:blank","status":429}';
        $e->setResponseBody(json_decode($resJson, true));
        $this->assertTrue($e->hasTooManyRequestError());
    }
}
