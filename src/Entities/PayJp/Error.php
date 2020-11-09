<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\PayJp;

use Payjp\Error\Base as PayJpErrorBase;
use Shucream0117\PhalconLib\Exceptions\InvalidApiResponseFormatException;

class Error
{
    const SAME_CARD = 'already_have_card';
    const TOKEN_ALREADY_USED = 'token_already_used';
    const INVALID_ID = 'invalid_id';
    const MISSING_CARD = 'missing_card';

    private string $errorCode;
    private int $statusCode;

    private function __construct(int $statusCode, string $errorCode)
    {
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
    }

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
        /** @var string|null $errorCode */
        $errorCode = $jsonBodyArray['error']['code'] ?? null;
        /** @var int|null $statusCode */
        $statusCode = $jsonBodyArray['error']['status'] ?? null;

        if (!$errorCode || !$statusCode) {
            throw new InvalidApiResponseFormatException('invalid format error response was returned from PAY.JP');
        }
        return new static($statusCode, $errorCode);
    }
}
