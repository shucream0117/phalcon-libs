<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

use Shucream0117\PhalconLib\Utils\StringUtil;

class NoEmoji extends AbstractCustomValidationRule
{
    protected $template = "Field :field must not have emoji";

    protected function checkValue($value): bool
    {
        return !StringUtil::hasEmoji($value);
    }
}
