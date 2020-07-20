<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Models;

use Phalcon\Mvc\Model;

abstract class AbstractModel extends Model
{
    public static string $COLUMN_ID = 'id';

    abstract public static function getTableName(): string;

    /**
     * 全体通して一度だけ実行される初期化メソッド
     */
    public function initialize(): void
    {
        $this->setSource(static::getTableName());
    }

    /**
     * インスタンスごとに実行される初期化メソッド
     */
    public function onConstruct(): void
    {
    }

    /**
     * getTableName()ではなく、クラス名で解決される
     * @param string $columnName
     * @param string|null $alias
     * @return string
     */
    public static function getFullyQualifiedColumnName(string $columnName, ?string $alias = null): string
    {
        $tableName = $alias ?: static::class;
        return "{$tableName}.{$columnName}";
    }
}
