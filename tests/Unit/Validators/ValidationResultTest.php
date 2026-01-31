<?php

namespace Tests\Unit\Validators;

use Shucream0117\PhalconLib\Validators\ValidationResult;
use Tests\Unit\TestBase;

class ValidationResultTest extends TestBase
{
    /**
     * @covers \Shucream0117\PhalconLib\Validators\ValidationResult::validated
     */
    public function testValidated()
    {
        $validationResult = new ValidationResult(['foo' => 'bar'], []);
        $this->assertSame('bar', $validationResult->validated('foo'));
        $this->assertSame('bar', $validationResult->validated('foo', 'default'));
        $this->assertSame('default', $validationResult->validated('baz', 'default'));
        $this->assertSame(['foo' => 'bar'], $validationResult->validated());
    }
}
