<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Exceptions;

// 共通ライブラリではAutoErrorResponseInterfaceの実装を考えたくないので敢えてExceptionを継承している
class TwitterApiErrorException extends \Exception
{
    /** @var array<array<string, mixed>> */
    protected array $errors;

    /**
     * @return array<array<string, mixed>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array<array<string, mixed>> $errors
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function has(int $errorCode): bool
    {
        foreach ($this->getErrors() as $error) {
            // 実行回数超過の場合はリクエストを中断して処理を継続
            if ($error['code'] === $errorCode) {
                return true;
            }
        }
        return false;
    }
}
