<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Validators;

use Phalcon\Messages\MessageInterface;
use Phalcon\Messages\Messages;

class ValidationError
{
    private string $field;

    /** @var ValidationErrorDetail[] */
    private array $details;

    public function __construct(string $field, array $details)
    {
        $this->field = $field;
        $this->details = $details;
    }

    /**
     * @param Messages $messages Validation::validate で返却されるMessagesオブジェクト
     * @return self[]
     */
    public static function createFromMessages(Messages $messages): array
    {
        $tmp = [];
        foreach ($messages as $m) { // 扱いやすくするためにフィールド名をキーにしたMessageのリスト形式にする
            $tmp[$m->getField()][] = $m;
        }

        /** @var ValidationError[] $result */

        $result = [];
        /**
         * @var string $fieldName
         * @var MessageInterface[] $messageList
         */
        foreach ($tmp as $fieldName => $messageList) {
            $result[] = new ValidationError($fieldName, array_map(
                fn(MessageInterface $m) => ValidationErrorDetail::createFromMessage($m),
                $messageList
            ));
        }
        return $result;
    }

    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return ValidationErrorDetail[]
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
