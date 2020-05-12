<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class FloatType extends AbstractCustomValidationRule
{
    protected $template = "Field :field must be float type";

    protected function checkValue($value): bool
    {
        return is_float($value);
    }
}
