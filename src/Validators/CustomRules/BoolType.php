<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class BoolType extends AbstractCustomValidationRule
{
    protected $template = "Field :field must be bool type";

    protected function checkValue($value): bool
    {
        return is_bool($value);
    }
}
