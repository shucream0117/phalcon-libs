<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Constants;

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
}
