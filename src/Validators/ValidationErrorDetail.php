<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators;

use Phalcon\Messages\MessageInterface;

class ValidationErrorDetail
{
    private string $validatorName; // エラーになったバリデーターのクラス名
    private string $message; // エラーメッセージ

    private function __construct(string $validatorName, string $message)
    {
        $this->validatorName = $validatorName;
        $this->message = $message;
    }

    public static function createFromMessage(MessageInterface $message): self
    {
        $exploded = explode('\\', $message->getType());
        $validatorName = array_pop($exploded);
        return new self($validatorName, $message->getMessage());
    }

    public function getValidatorName(): string
    {
        return $this->validatorName;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
