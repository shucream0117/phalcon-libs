<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class ArrayType extends AbstractCustomValidationRule
{
    protected $template = "Field :field must be array type";

    protected function checkValue($value): bool
    {
        return is_array($value);
    }
}
