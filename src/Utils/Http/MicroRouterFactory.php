<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils\Http;

use Phalcon\Mvc\Micro\Collection;
use Phalcon\Mvc\Micro\CollectionInterface;

class MicroRouterFactory
{
    public static function create(string $controllerClassName, bool $lazy = true): CollectionInterface
    {
        return (new Collection())->setHandler($controllerClassName, $lazy);
    }
}
