<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

/**
 * options
 * [
 *  include_zero => true or false
 * ]
 */
class NaturalNumber extends AbstractCustomValidationRule
{
    protected $template = "Field :field must be natural number";

    protected function checkValue($value): bool
    {
        if (is_float($value) || is_bool($value)) {
            return false;
        }
        if (filter_var($value, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_OCTAL) === false) {
            return false;
        }
        $includeZero = $this->getOption('include_zero', true);
        return $includeZero ? (0 <= $value) : (1 <= $value);
    }
}
