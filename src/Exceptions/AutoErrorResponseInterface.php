<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Exceptions;

// これを例外クラスに実装しておけば、スローするだけで自動的にキャッチしてエラーレスポンスを返せる
interface AutoErrorResponseInterface
{
    public function getErrorCode();

    public function getErrorMessage(): string;

    public function getStatusCode(): int;
}
