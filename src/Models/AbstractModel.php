<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Models;

use Phalcon\Mvc\Model;

abstract class AbstractModel extends Model
{
    const COLUMN_ID = 'id';
    
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
}
