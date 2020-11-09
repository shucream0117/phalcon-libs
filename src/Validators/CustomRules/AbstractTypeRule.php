<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

use Phalcon\Validation;

// 型バリデーションを行うルールクラスの基底
// allowNullオプションをfalseにするとnullを不許可とすることが出来る。(デフォルトtrue)
abstract class AbstractTypeRule extends AbstractCustomValidationRule
{
    public function validate(Validation $validator, $field): bool
    {
        $allowNull = $this->getOption('allowNull', true);
        if ($allowNull && is_null($validator->getValue($field))) {
            return false;
        }
        return parent::validate($validator, $field);
    }
}
