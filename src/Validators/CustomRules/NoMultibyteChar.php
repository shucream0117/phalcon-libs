<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

class NoMultibyteChar extends AbstractCustomValidationRule
{
    protected $template = "Field :field must not have multibyte chars";

    protected function checkValue($value): bool
    {
        return strlen($value) === mb_strlen($value);
    }
}
