<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Constants;

class Country
{
    use ConstantsTrait;

    const JAPAN = 'japan';

    protected static array $text = [
        self::JAPAN => '日本',
    ];
}
