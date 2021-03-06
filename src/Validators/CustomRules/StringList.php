<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class StringList extends AbstractCustomValidationRule
{
    protected $template = "Field :field must be list of string values";

    protected function checkValue($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        if (array_values($value) !== $value) {
            return false;
        }
        foreach ($value as $v) {
            if (!is_string($v)) {
                return false;
            }
        }
        return true;
    }
}
