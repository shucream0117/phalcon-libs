<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Exceptions;

use Throwable;

// 共通ライブラリではAutoErrorResponseInterfaceの実装を考えたくないので敢えてExceptionを継承している
class TwitterApiErrorException extends \Exception
{
    /** @var array<array<string, mixed>> */
    protected array $errors = [];
    /** @var string */
    protected string $responseBody = '';

    /**
     * @return array<array<string, mixed>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    /**
     * @param array<array<string, mixed>> $errors
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @param string $responseBody
     */
    public function setResponseBody(string $responseBody): void
    {
        $this->responseBody = $responseBody;
    }

    public function has(int $errorCode): bool
    {
        foreach ($this->getErrors() as $error) {
            // 実行回数超過の場合はリクエストを中断して処理を継続
            if (isset($error['code']) && $error['code'] === $errorCode) {
                return true;
            }
        }
        return false;
    }
}
