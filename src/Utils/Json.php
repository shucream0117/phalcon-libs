<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

class Json
{
    protected const JSON_ENCODE_DEFAULT_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR;
    protected const JSON_DECODE_DEFAULT_OPTIONS = JSON_THROW_ON_ERROR;

    /**
     * decode結果を連想配列で返す
     *
     * @param string $data
     * @param int $options
     * @param int $depth
     * @return array
     */
    public static function decode(
        string $data,
        int $options = self::JSON_DECODE_DEFAULT_OPTIONS,
        int $depth = 512
    ): array {
        return \Phalcon\Helper\Json::decode($data, true, $depth, $options);
    }

    /**
     * @param \JsonSerializable|array|\Object $data
     * @param int $options
     * @param int $depth
     * @return string
     */
    public static function encode(
        $data,
        int $options = self::JSON_ENCODE_DEFAULT_OPTIONS,
        int $depth = 512
    ): string  {
        return \Phalcon\Helper\Json::encode($data, $options, $depth);
    }
}
