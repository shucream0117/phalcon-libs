<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Exceptions;

use RuntimeException;

abstract class AbstractRuntimeException extends RuntimeException implements AutoErrorResponseInterface
{

}
