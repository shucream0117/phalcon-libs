<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class StringType extends AbstractCustomValidationRule
{
    protected $template = "Field :field must be string type";

    protected function checkValue($value): bool
    {
        return is_string($value);
    }
}
