<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Models\Behaviors;

use DateTimeImmutable;
use Exception;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Shucream0117\PhalconLib\Utils\Date;

trait TimestampTrait
{
    /**
     * フレームワークとの相性の問題であえてtyped parameterにしないでおく。
     * 初期化前にアクセスされるとエラーになるため。
     *
     * @var string
     */
    protected $created_at;

    /** @var string */
    protected $updated_at;

    public static string $COLUMN_CREATED_AT = 'created_at';
    public static string $COLUMN_UPDATED_AT = 'updated_at';

    protected function getCreatedAtBehavior(): Timestampable
    {
        return new Timestampable([
            'beforeCreate' => [
                'field' => static::$COLUMN_CREATED_AT,
                'generator' => fn() => Date::mysqlDatetimeFormat(Date::createDateTimeImmutable()),
            ],
        ]);
    }

    protected function getUpdatedAtBehavior(): Timestampable
    {
        return new Timestampable([
            'beforeCreate' => [
                'field' => static::$COLUMN_UPDATED_AT,
                'format' => Date::mysqlDatetimeFormat(Date::createDateTimeImmutable()),
            ],
            'beforeUpdate' => [
                'field' => static::$COLUMN_UPDATED_AT,
                'generator' => fn() => Date::mysqlDatetimeFormat(Date::createDateTimeImmutable()),
            ],
        ]);
    }

    /**
     * @return DateTimeImmutable
     * @throws Exception
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return Date::createDateTimeImmutable($this->created_at);
    }

    /**
     * @return DateTimeImmutable
     * @throws Exception
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return Date::createDateTimeImmutable($this->updated_at);
    }
}
