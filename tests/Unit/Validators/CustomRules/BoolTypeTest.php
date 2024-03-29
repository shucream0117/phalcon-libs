<?php

namespace Tests\Unit\Validators\CustomRules;

use Shucream0117\PhalconLib\Validators\AbstractValidator;
use Shucream0117\PhalconLib\Validators\CustomRules\BoolType;
use Shucream0117\PhalconLib\Validators\ValidationResult;
use Shucream0117\PhalconLib\Validators\ValidationRuleSet;
use Tests\Unit\TestBase;

class BoolTypeTest extends TestBase
{
    private function getValidator()
    {
        return new class() extends AbstractValidator {
            public function testValidate(array $params): ValidationResult
            {
                return $this->validate($params, [
                    new ValidationRuleSet('is_something', [
                        new BoolType($this->optionCancelOnFail()),
                    ]),
                ]);
            }
        };
    }

    public function testCheckValueWithInvalidValue()
    {
        $validationResult = $this->getValidator()->testValidate(['is_something' => 'true']);
        $this->assertSame('is_something', $validationResult->getErrors()[0]->getField());
        $this->assertSame('Field is_something must be bool type', $validationResult->getErrors()[0]->getDetails()[0]->getMessage());
    }

    public function testCheckValueWithValidValue()
    {
        $validationResult = $this->getValidator()->testValidate(['is_something' => true]);
        $this->assertEmpty($validationResult->getErrors());
        $this->assertSame(true, $validationResult->validated('is_something'));

        $validationResult = $this->getValidator()->testValidate(['is_something' => false]);
        $this->assertEmpty($validationResult->getErrors());
        $this->assertSame(false, $validationResult->validated('is_something'));
    }
}
