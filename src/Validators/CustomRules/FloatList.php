<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class FloatList extends AbstractTypeRule
{
    protected $template = "Field :field must be list of float values";

    protected function checkValue($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        if (array_values($value) !== $value) {
            return false;
        }
        foreach ($value as $v) {
            if (!is_float($v)) {
                return false;
            }
        }
        return true;
    }
}
