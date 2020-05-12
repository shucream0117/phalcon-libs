<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use DateTimeZone;
use DateTime;

class Date
{
    /**
     * ミリ秒
     * @return int
     */
    public static function timestampMilliSec(): int
    {
        return intval(microtime(true) * 1000);
    }

    /**
     * 必要であれば継承したクラスでオーバーライドする
     * @return DateTimeZone
     */
    public static function getDefaultTimezone(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    public static function getJstTimezone(): DateTimeZone
    {
        return new DateTimeZone('Asia/Tokyo');
    }

    /**
     * MySQLのDatetime型のフォーマットの文字列を返す
     * @param DateTime $dateTime
     * @param bool $decimal
     * @return string
     */
    public static function mysqlDatetimeFormat(DateTime $dateTime, bool $decimal = false): string
    {
        $format = 'Y-m-d H:i:s';
        if ($decimal) {
            $format .= '.u';
        }
        return $dateTime->format($format);
    }

    /**
     * MysqlのDateフォーマットの文字列を返す
     * @param DateTime $dateTime
     * @return string
     */
    public static function mysqlDateFormat(DateTime $dateTime): string
    {
        return $dateTime->format('Y-m-d');
    }

    /**
     * @param string $time
     * @param DateTimeZone|null $timezone
     * @return DateTime
     */
    public static function createDateTime(string $time = 'now', ?DateTimeZone $timezone = null): DateTime
    {
        if (!$timezone) {
            $timezone = static::getDefaultTimezone();
        }
        return new DateTime($time, $timezone);
    }

    /**
     * 未来のDateTimeを返す($nowに指定された秒数を足す)
     *
     * @param int $additionalSec
     * @param DateTime|null $now
     * @param DateTimeZone|null $timezone
     * @return DateTime
     */
    public static function createFutureDateTime(int $additionalSec, ?DateTime $now = null, ?DateTimeZone $timezone = null): DateTime
    {
        if (!$now) {
            $now = static::createDateTime();
        }
        return self::createDateTimeFromTimeStamp($now->getTimestamp() + $additionalSec, $timezone);
    }

    /**
     * 過去のDateTimeを返す
     * @param int $pastSec
     * @param DateTime|null $now
     * @param DateTimeZone|null $timezone
     * @return DateTime
     */
    public static function createPastDateTime(int $pastSec, ?DateTime $now = null, ?\DateTimeZone $timezone = null): DateTime
    {
        return self::createFutureDateTime($pastSec * -1, $now, $timezone);
    }

    /**
     * @param int $timestamp
     * @param DateTimeZone|null $timezone
     * @return DateTime
     */
    public static function createDateTimeFromTimeStamp(int $timestamp, ?DateTimeZone $timezone = null): DateTime
    {
        if (!$timezone) {
            $timezone = static::getDefaultTimezone();
        }
        $dt = new DateTime('now', $timezone);
        $dt->setTimestamp($timestamp);
        return $dt;
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int|null $hour
     * @param int|null $min
     * @param int|null $sec
     * @param DateTimeZone|null $timezone
     * @return DateTime
     */
    public static function createDateTimeFromYmd(
        int $year,
        int $month,
        int $day,
        ?int $hour = null,
        ?int $min = null,
        ?int $sec = null,
        ?DateTimeZone $timezone = null
    ): DateTime {
        if (is_null($hour) || $hour < 0 || 23 < $hour) {
            $hour = '00';
        } elseif ($hour < 10) {
            $hour = "0{$hour}";
        }

        if (is_null($min) || $min < 0 || 59 < $min) {
            $min = '00';
        } elseif ($min < 10) {
            $min = "0{$min}";
        }

        if (is_null($sec) || $sec < 0 || 59 < $sec) {
            $sec = '00';
        } elseif ($sec < 10) {
            $sec = "0{$sec}";
        }
        return DateTime::createFromFormat(
            'Y-m-d H:i:s',
            "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}",
            $timezone ?: static::getDefaultTimezone()
        );
    }
}
