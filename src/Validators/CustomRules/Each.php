<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators\CustomRules;

use Phalcon\Validation;
use Shucream0117\PhalconLib\Validators\AbstractValidator;
use Shucream0117\PhalconLib\Validators\ValidationResult;
use Shucream0117\PhalconLib\Validators\ValidationRuleSet;

/*
 * リストの個々の要素に対してバリデーションを行う
 *
 * [
 *   "list" => ["hoge, "fuga", "piyo"], // バリデーション対象のリスト
 *   "rules" => [
 *       new PresenceOf($this->optionCancelOnFail()),
 *       new StringType($this->optionCancelOnFail()),
 *   ]
 * ]
 */

class Each extends Validation\Validator\Callback
{
    protected $template = 'Field :field has invalid properties';

    public function __construct(array $options = [])
    {
        $options['callback'] = function (): bool {
            $ruleSetList = $this->getOption('rules') ?: [];
            $list = $this->getOption('list') ?: [];

            $validator = new class extends AbstractValidator {
                public function validate(array $params, array $rules): ValidationResult
                {
                    return parent::validate($params, $rules); // TODO: Change the autogenerated stub
                }
            };
            foreach ($list as $v) {
                $result = $validator->validate(['v' => $v], [new ValidationRuleSet('v', $ruleSetList)]);
                if ($result->getErrors()) {
                    return false;
                }
            }
            return true;
        };
        parent::__construct($options);
    }
}
