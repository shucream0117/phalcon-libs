<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use Phalcon\Helper\Str;

class StringUtil extends Str
{
    /**
     * TODO あとで https://github.com/siahr/igo-php この辺をつかってやる
     *
     * 任意の文字列(漢字・ひらがな・カタカナも可)をアルファベット表記にした場合の先頭文字を取得する
     * @param string $anyStr
     * @param bool $capitalize
     * @return string
     */
    public static function getInitialAlphabet(string $anyStr, bool $capitalize = true): string
    {
        $letter = 'A';
        return $capitalize ? strtoupper($letter) : strtolower($letter);
    }

    /**
     * 絵文字を除去する
     * 
     * @param string $str
     * @return string
     */
    public static function removeEmoji(string $str): string
    {
        return preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $str);
    }
}
