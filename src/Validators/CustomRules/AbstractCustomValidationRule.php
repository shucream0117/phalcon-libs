<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

use Phalcon\Validation;
use Phalcon\Validation\AbstractValidator;

abstract class AbstractCustomValidationRule extends AbstractValidator
{
    public function validate(Validation $validator, $field): bool
    {
        if ($this->checkValue($validator->getValue($field))) {
            return true;
        }
        $validator->appendMessage($this->messageFactory($validator, $field));
        return false;
    }

    abstract protected function checkValue($value): bool;
}
