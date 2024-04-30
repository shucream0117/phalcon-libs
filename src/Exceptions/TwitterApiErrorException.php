<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Exceptions;

// 共通ライブラリではAutoErrorResponseInterfaceの実装を考えたくないので敢えてExceptionを継承している
use Shucream0117\PhalconLib\Constants\TwitterErrorCode;

class TwitterApiErrorException extends \Exception
{
    /** @var array<array<string, mixed>> */
    protected array $errors = [];
    /** @var array<string, mixed> */
    protected array $responseBody = [];

    /**
     * @return array<array<string, mixed>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResponseBody(): array
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
     * @param array $responseBody
     */
    public function setResponseBody(array $responseBody): void
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

    /**
     * 認証エラーかどうかを判定する(v1.1とv2の両方に対応)
     * @return bool
     */
    public function hasUnauthorizedError(): bool
    {
        // v1.1
        if ($this->has(TwitterErrorCode::ERROR_CODE_INVALID_OR_EXPIRED_TOKEN)) {
            return true;
        }

        // v2 の方は {"title":"Unauthorized","type":"about:blank","status":401,"detail":"Unauthorized"} という形式で返ってくる
        $body = $this->getResponseBody();
        $status = $body['status'] ?? null;
        if ($status === 401) {
            return true;
        }
        return false;
    }
}
