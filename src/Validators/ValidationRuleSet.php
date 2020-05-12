<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators;

use Phalcon\Validation\AbstractValidator;

class ValidationRuleSet
{
    private string $field;

    /** @var AbstractValidator[] */
    private array $rules;

    private bool $required;

    /**
     * @param string $field
     * @param AbstractValidator[] $rules
     */
    public function __construct(string $field, array $rules)
    {
        $this->field = $field;
        $this->rules = $rules;
    }

    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return AbstractValidator[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }
}
