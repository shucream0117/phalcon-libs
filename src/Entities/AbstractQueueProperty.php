<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities;

/**
 * クラスプロパティはprotectedで定義すること
 */
abstract class AbstractQueueProperty
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * キューワーカーに寄って取り出されたデータをエンティティクラスに変換する氏
     * @param array $data
     * @return static
     */
    abstract public static function createFromArray(array $data): self;
}
