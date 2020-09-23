<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\JsonResponses;

use JsonSerializable;
use Phalcon\Di\Injectable;
use stdClass;

abstract class AbstractResponseBody extends Injectable implements JsonSerializable
{
    /**
     * 空配列のときにもJSONのオブジェクト形式で返却したいフィールド名を列挙する
     * @var string[]
     */
    protected static array $objectTypeFields = [];

    /**
     * @return array|stdClass
     */
    public function jsonSerialize()
    {
        if (!$vars = get_object_vars($this)) {
            return new stdClass();
        }

        if (!static::$objectTypeFields) {
            return $vars;
        }

        foreach (static::$objectTypeFields as $field) {
            if (($vars[$field] ?? null) === []) {
                $vars[$field] = new stdClass();
            }
        }
        return $vars;
    }
}
