<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Exceptions;

use Exception;

// 共通ライブラリではAutoErrorResponseInterfaceの実装を考えたくないので敢えてExceptionを継承している
class InvalidAccessTokenException extends Exception
{
}
