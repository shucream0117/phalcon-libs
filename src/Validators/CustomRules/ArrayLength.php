<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

/*
 * [
 *   'max' => 3,
 *   'min' => 1,
 * ]
 * or
 * [
 *   'equal' => 2,
 * ]
 */

class ArrayLength extends AbstractCustomValidationRule
{
    protected $template = "Field :field has invalid numbers of elements";

    protected function checkValue($value): bool
    {
        $count = count($value);
        $equal = $this->getOption('equal');
        if (is_int($equal)) {
            return $count === $equal;
        }

        $max = $this->getOption('max');
        if (is_int($max) && ($max < $count)) {
            return false;
        }
        $min = $this->getOption('min');
        if (is_int($min) && ($count < $min)) {
            return false;
        }
    }
}
