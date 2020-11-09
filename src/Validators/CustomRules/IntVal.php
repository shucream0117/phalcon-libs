<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class IntVal extends AbstractTypeRule
{
    protected $template = "Field :field must be int val";

    protected function checkValue($value): bool
    {
        if (is_float($value) || is_bool($value)) {
            return false;
        }
        return filter_var($value, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_OCTAL) !== false;
    }
}
