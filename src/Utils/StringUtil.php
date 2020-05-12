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
}
