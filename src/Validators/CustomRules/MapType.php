<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class MapType extends AbstractTypeRule
{
    protected $template = "Field :field must be map type";

    protected function checkValue($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        return array_values($value) !== $value;
    }
}
