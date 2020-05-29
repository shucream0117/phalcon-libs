<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators;

use Phalcon\Di\Injectable;
use Phalcon\Validation;

abstract class AbstractValidator extends Injectable
{
    protected function optionCancelOnFail(): array
    {
        return ['cancelOnFail' => true];
    }

    /**
     * @param array $params
     * @param ValidationRuleSet[] $rules
     * @return ValidationResult
     */
    protected function validate(array $params, array $rules): ValidationResult
    {
        $targetParams = [];
        $validator = new Validation();
        foreach ($rules as $rule) {
            $field = $rule->getField();

            /*
             * 必須ではない場合(=PresenceOfがない場合) に、$params[$field]が存在しない場合はそもそもバリデーションに含めない。
             * そうしないとオプショナルパラメータが送られていない場合もエラーを出してしまうため。
             */
            $hasPresenceOf = false;
            foreach ($rule->getRules() as $r) {
                if ($r instanceof Validation\Validator\PresenceOf) {
                    $hasPresenceOf = true;
                    break;
                }
            }
            if (!$hasPresenceOf && !array_key_exists($field, $params)) {
                continue;
            }

            $validator->rules($field, $rule->getRules());
            if (array_key_exists($field, $params)) {
                $targetParams[$field] = $params[$field];
            }
        }
        $messages = $validator->validate($targetParams);
        $validationErrors = ValidationError::createFromMessages($messages);

        // $paramsの中からエラーがあった項目を消して、残ったものをバリデーション済みの値としてValidationResultに渡す
        foreach ($validationErrors as $error) {
            $field = $error->getField();
            if (array_key_exists($field, $params)) {
                unset($targetParams[$error->getField()]);
            }
        }
        return new ValidationResult($targetParams, $validationErrors);
    }
}
