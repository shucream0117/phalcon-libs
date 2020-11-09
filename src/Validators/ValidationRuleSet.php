<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators;

use Phalcon\Validation\AbstractValidator;
use Phalcon\Validation\Validator\PresenceOf;

class ValidationRuleSet
{
    private string $field;

    /** @var AbstractValidator[] */
    private array $rules;

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
        foreach ($this->getRules() as $r) {
            if ($r instanceof PresenceOf) {
                return true;
            }
        }
        return false;
    }
}
