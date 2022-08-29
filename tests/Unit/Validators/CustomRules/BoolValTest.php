<?php

namespace Tests\Unit\Validators\CustomRules;

use Shucream0117\PhalconLib\Validators\AbstractValidator;
use Shucream0117\PhalconLib\Validators\CustomRules\BoolVal;
use Shucream0117\PhalconLib\Validators\ValidationResult;
use Shucream0117\PhalconLib\Validators\ValidationRuleSet;
use Tests\Unit\TestBase;

class BoolValTest extends TestBase
{
    private function getValidator()
    {
        return new class() extends AbstractValidator {
            public function testValidate(array $params): ValidationResult
            {
                return $this->validate($params, [
                    new ValidationRuleSet('is_something', [
                        new BoolVal($this->optionCancelOnFail()),
                    ]),
                ]);
            }
        };
    }

    public function testCheckValueWithInvalidValue()
    {
        $validationResult = $this->getValidator()->testValidate(['is_something' => 'invalid']);
        $this->assertSame('is_something', $validationResult->getErrors()[0]->getField());
        $this->assertSame('Field is_something must be bool val', $validationResult->getErrors()[0]->getDetails()[0]->getMessage());
    }

    public function testCheckValueWithValidValue()
    {
        $validationResult = $this->getValidator()->testValidate(['is_something' => 'true']);
        $this->assertEmpty($validationResult->getErrors());
        $this->assertSame('true', $validationResult->validated('is_something'));

        $validationResult = $this->getValidator()->testValidate(['is_something' => 'false']);
        $this->assertEmpty($validationResult->getErrors());
        $this->assertSame('false', $validationResult->validated('is_something'));

        $validationResult = $this->getValidator()->testValidate(['is_something' => true]);
        $this->assertEmpty($validationResult->getErrors());
        $this->assertSame(true, $validationResult->validated('is_something'));

        $validationResult = $this->getValidator()->testValidate(['is_something' => false]);
        $this->assertEmpty($validationResult->getErrors());
        $this->assertSame(false, $validationResult->validated('is_something'));

        $validationResult = $this->getValidator()->testValidate(['is_something' => 'TRUE']);
        $this->assertEmpty($validationResult->getErrors());
        $this->assertSame('TRUE', $validationResult->validated('is_something'));

        $validationResult = $this->getValidator()->testValidate(['is_something' => 'falSE']);
        $this->assertEmpty($validationResult->getErrors());
        $this->assertSame('falSE', $validationResult->validated('is_something'));
    }
}
