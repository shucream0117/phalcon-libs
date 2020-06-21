<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Constants;

use InvalidArgumentException;

class MimeType
{
    const JPEG = 'image/jpeg';
    const PNG = 'image/png';
    const GIF = 'image/gif';
    const WEBP = 'image/webp';
    const SVG = 'image/svg+xml';
    const BMP = 'image/bmp';

    const MP4 = 'video/mp4';
    const MOV = 'video/quicktime';
    const WEBM = 'video/webm';

    const JSON = 'application/json';

    protected const EXT_MAP = [
        self::JPEG => 'jpg',
        self::PNG => 'png',
        self::GIF => 'gif',
        self::WEBP => 'webp',
        self::SVG => 'svg',
        self::BMP => 'bmp',
        self::MP4 => 'mp4',
        self::MOV => 'mov',
        self::WEBM => 'webm',
        self::JSON => 'json',
    ];

    /**
     * 大文字小文字の決まりがないため、比較メソッドを用意
     * @param string $a
     * @param string $b
     * @return bool
     */
    public static function isEqual(string $a, string $b): bool
    {
        return strtolower($a) === strtolower($b);
    }

    /**
     * $mimeType が $mimeTypes に含まれるかどうか。
     * (大文字小文字の差異を考慮)
     * @param string $mimeType
     * @param array $mimeTypes
     * @return bool
     */
    public static function isIncluded(string $mimeType, array $mimeTypes): bool
    {
        $mimeTypes = array_map(fn(string $m) => strtolower($m), $mimeTypes);
        return in_array(strtolower($mimeType), $mimeTypes);
    }

    /**
     * MimeTypeに対応する拡張子を返す
     *
     * @param string $mimeType
     * @param bool $uppercase
     * @return string
     */
    public static function mimeToExtension(string $mimeType, bool $uppercase = false): string
    {
        if ($ext = (self::EXT_MAP[$mimeType] ?? null)) {
            return $uppercase? strtoupper($ext) : $ext;
        }
        throw new InvalidArgumentException("invalid mime {$mimeType} is given");
    }
}
