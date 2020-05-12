<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class IntType extends AbstractCustomValidationRule
{
    protected $template = "Field :field must be int type";

    protected function checkValue($value): bool
    {
        return is_int($value);
    }
}
