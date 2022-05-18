<?php

namespace Tests\Unit\Validators;

use Phalcon\Validation\Validator\PresenceOf;
use Shucream0117\PhalconLib\Validators\AbstractValidator;
use Shucream0117\PhalconLib\Validators\CustomRules\IntType;
use Shucream0117\PhalconLib\Validators\CustomRules\StringType;
use Shucream0117\PhalconLib\Validators\ValidationResult;
use Shucream0117\PhalconLib\Validators\ValidationRuleSet;
use Tests\Unit\TestBase;

class AbstractValidatorTest extends TestBase
{
    /**
     * バリデーションした値のみが結果に入るかどうかのテスト
     */
    public function testValidationResultKeys()
    {
        $validator = new class() extends AbstractValidator {
            public function testValidate(array $params): ValidationResult
            {
                return $this->validate($params, [
                    new ValidationRuleSet('key1', [
                        new PresenceOf($this->optionCancelOnFail()),
                        new StringType($this->optionCancelOnFail()),
                    ]),
                    new ValidationRuleSet('key2', [
                        new StringType($this->optionCancelOnFail()),
                    ]),
                    new ValidationRuleSet('key3', [
                        new StringType($this->optionCancelOnFail()),
                    ]),
                    new ValidationRuleSet('key4', [
                        new IntType($this->optionCancelOnFail()),
                    ]),
                ]);
            }
        };

        $result = $validator->testValidate([
            'key0' => 'このキーは結果に入らない',
            'key1' => 'test',
            'key2' => 'test2',
            // key3は渡さない
            'key4' => '1', // error
        ]);
        $expected = [
            'key1' => 'test',
            'key2' => 'test2',
        ];
        $this->assertSame($expected, $result->validated());
    }
}
