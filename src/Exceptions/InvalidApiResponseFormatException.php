<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Exceptions;

use RuntimeException;

// 共通ライブラリではAutoErrorResponseInterfaceの実装を考えたくないので敢えてRuntimeExceptionを継承している
class InvalidApiResponseFormatException extends RuntimeException
{
}
