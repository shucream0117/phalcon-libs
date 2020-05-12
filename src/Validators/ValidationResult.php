<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators;

class ValidationResult
{
    private array $validated;

    /** @var ValidationError[] */
    private array $errors;

    /**
     * @param array $validated
     * @param ValidationError[] $errors
     */
    public function __construct(array $validated, array $errors)
    {
        $this->validated = $validated;
        $this->errors = $errors;
    }

    /**
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function validated(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->validated;
        }
        return $this->validated[$key] ?? $default;
    }

    /**
     * @return ValidationError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
