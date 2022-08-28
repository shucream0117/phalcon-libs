<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

/**
 * クエリストリングで boolean を受け取る場合のためのルール
 */
class BoolVal extends AbstractTypeRule
{
    protected $template = "Field :field must be bool val";

    protected function checkValue($value): bool
    {
        return $value === 'true' || $value === 'false';
    }
}
