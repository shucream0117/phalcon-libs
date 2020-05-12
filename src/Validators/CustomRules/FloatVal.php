<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class FloatVal extends AbstractCustomValidationRule
{
    protected $template = "Field :field must be float val";

    protected function checkValue($value): bool
    {
        return is_float(filter_var($value, FILTER_VALIDATE_FLOAT));
    }
}
