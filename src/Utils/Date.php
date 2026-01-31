<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use DateTime;

class Date
{
    private static ?DateTimeZone $defaultTimezone = null;

    public static function setDefaultTimezone(DateTimeZone $tz): void
    {
        static::$defaultTimezone = $tz;
    }

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
        if (static::$defaultTimezone) {
            return static::$defaultTimezone;
        }
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
    public static function mysqlDatetimeFormat(DateTimeInterface $dateTime, bool $decimal = false): string
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
    public static function mysqlDateFormat(DateTimeInterface $dateTime): string
    {
        return $dateTime->format('Y-m-d');
    }

    /**
     * @param string $time
     * @param DateTimeZone|null $timezone
     * @return DateTimeImmutable
     */
    public static function createDateTimeImmutable(string $time = 'now', ?DateTimeZone $timezone = null): DateTimeImmutable
    {
        if (!$timezone) {
            $timezone = static::getDefaultTimezone();
        }
        return new DateTimeImmutable($time, $timezone);
    }

    /**
     * 未来の日付を返す($nowに指定された秒数を足す)
     *
     * @param int $additionalSec
     * @param DateTimeImmutable|null $now
     * @param DateTimeZone|null $timezone
     * @return DateTimeImmutable
     */
    public static function createFutureDateTimeImmutable(int $additionalSec, ?DateTimeInterface $now = null, ?DateTimeZone $timezone = null): DateTimeImmutable
    {
        if (!$now) {
            $now = static::createDateTimeImmutable();
        }
        return self::createDateTimeImmutableFromTimeStamp($now->getTimestamp() + $additionalSec, $timezone);
    }

    /**
     * 過去の日付を返す
     * @param int $pastSec
     * @param DateTimeInterface|null $now
     * @param DateTimeZone|null $timezone
     * @return DateTimeImmutable
     */
    public static function createPastDateTimeImmutable(int $pastSec, ?\DateTimeInterface $now = null, ?\DateTimeZone $timezone = null): DateTimeImmutable
    {
        return self::createFutureDateTimeImmutable($pastSec * -1, $now, $timezone);
    }

    /**
     * Unixタイムスタンプから生成
     * @param int $timestamp
     * @param DateTimeZone|null $timezone
     * @return DateTimeImmutable
     */
    public static function createDateTimeImmutableFromTimeStamp(int $timestamp, ?DateTimeZone $timezone = null): DateTimeImmutable
    {
        if (!$timezone) {
            $timezone = static::getDefaultTimezone();
        }
        $dt = new DateTimeImmutable('now', $timezone);
        return $dt->setTimestamp($timestamp);
    }

    /**
     * ミリ秒から生成
     * @param int $timestampMilliSec
     * @param DateTimeZone|null $timezone
     * @return DateTimeImmutable
     * @throws \Exception
     */
    public static function createDateTimeImmutableFromTimeStampMilliSec(int $timestampMilliSec, ?DateTimeZone $timezone = null): DateTimeImmutable
    {
        $timestamp = (int)floor($timestampMilliSec / 1000);
        return self::createDateTimeImmutableFromTimeStamp($timestamp, $timezone);
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int|null $hour
     * @param int|null $min
     * @param int|null $sec
     * @param DateTimeZone|null $timezone
     * @return DateTimeImmutable
     */
    public static function createDateTimeImmutableFromYmd(
        int $year,
        int $month,
        int $day,
        ?int $hour = null,
        ?int $min = null,
        ?int $sec = null,
        ?DateTimeZone $timezone = null
    ): DateTimeImmutable {
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
        return DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}",
            $timezone ?: static::getDefaultTimezone()
        );
    }

    ////////////////////////////////////////////////
    /// 以下は旧来のDateTimeクラスを使ったメソッド
    /// これらは廃止予定
    /// 今後はDateTimeImmutableを使うこと
    ////////////////////////////////////////////////

    /**
     * @param string $time
     * @param DateTimeZone|null $timezone
     * @return DateTime
     * @deprecated use createDateTimeImmutable() instead
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
     * @deprecated use createFutureDateTimeImmutable() instead
     */
    public static function createFutureDateTime(int $additionalSec, ?DateTimeInterface $now = null, ?DateTimeZone $timezone = null): DateTime
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
     * @deprecated use createPastDateTimeImmutable() instead
     */
    public static function createPastDateTime(int $pastSec, ?DateTimeInterface $now = null, ?\DateTimeZone $timezone = null): DateTime
    {
        return self::createFutureDateTime($pastSec * -1, $now, $timezone);
    }

    /**
     * @param int $timestamp
     * @param DateTimeZone|null $timezone
     * @return DateTime
     * @deprecated use createDateTimeImmutableFromTimeStamp() instead
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
     * ミリ秒から生成
     * @param int $timestampMilliSec
     * @param DateTimeZone|null $timezone
     * @return DateTime
     * @throws \Exception
     * @deprecated use createDateTimeImmutableFromTimeStampMilliSec() instead
     */
    public static function createDateTimeFromTimeStampMilliSec(int $timestampMilliSec, ?DateTimeZone $timezone = null): DateTime
    {
        $timestamp = (int)floor($timestampMilliSec / 1000);
        return self::createDateTimeFromTimeStamp($timestamp, $timezone);
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
     * @deprecated use createDateTimeImmutableFromYmd() instead
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
