<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Constants;

use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

// 定数クラスで使うと便利になるトレイト

/**
 * @property array $text
 * @property array $textByLang
 */
trait ConstantsTrait
{
    /**
     * static $text という配列を定義すると定数にマッチするテキストを返してくれる。
     * @param string|int $constant 定数の値
     * @return string 定数に対応するテキスト
     * @throws RuntimeException static $text が未定義の場合に投げる
     * @throws InvalidArgumentException 定義されていない定数を指定された場合に投げる
     */
    public static function getText($constant): string
    {
        if (!property_exists(get_class(), 'text')) {
            throw new RuntimeException('static $text is required by ConstantsWithTextTrait');
        }

        if (array_key_exists($constant, static::$text)) {
            return static::$text[$constant];
        }
        throw new InvalidArgumentException("no such constant code: $constant");
    }

    /**
     * static $text という配列を定義すると定数にマッチするテキストを返してくれる。
     * @param string|int $constant 定数の値
     * @return string 定数に対応するテキスト
     * @throws RuntimeException static $text が未定義の場合に投げる
     * @throws InvalidArgumentException 定義されていない定数を指定された場合に投げる
     */
    public static function getTextByLang($constant, string $language): string
    {
        if (!property_exists(get_class(), 'textByLang')) {
            throw new RuntimeException('static $textByLang is required by ConstantsWithTextTrait');
        }

        if (array_key_exists($constant, static::$textByLang)
            && array_key_exists($language, static::$textByLang[$constant])) {
            return static::$textByLang[$constant][$language];
        }
        throw new InvalidArgumentException("no such constant code: $constant");
    }

    public static function getAsList(): array
    {
        return array_values(self::getAsAssocArray());
    }

    /**
     * @return array
     */
    public static function getAsAssocArray(): array
    {
        // TODO ｳｰﾝ...
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}
