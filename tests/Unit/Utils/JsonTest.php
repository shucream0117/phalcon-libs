<?php

namespace Tests\Unit\Utils;

use Shucream0117\PhalconLib\Utils\Json;
use Tests\Unit\TestBase;

class JsonTest extends TestBase
{
    /**
     * @param string $input
     * @param array $expected
     * @dataProvider dataProviderForTestDecode
     */
    public function testDecode(string $input, array $expected)
    {
        $this->assertSame($expected, Json::decode($input));
    }

    /**
     * testDecode のデータプロバイダ
     */
    public function dataProviderForTestDecode(): array
    {
        $altChar = "\u{fffd}";
        return [
            // 正常系
            ['{"key1":"value1","key2":"value2"}', ['key1' => 'value1', 'key2' => 'value2']],

            // 不正文字混入(\x80 - \xFF)
            ["{\"key1\":\"\x80\"}", ['key1' => "{$altChar}"]],
            ["{\"key1\":\"value1\",\"key2\":\"\x80\xFF\"}", ['key1' => "value1", 'key2' => "{$altChar}{$altChar}"]],
            ["{\"key1\":{\"nestKey1\":\"value1\x80value1\"}}", ['key1' => ['nestKey1' => "value1{$altChar}value1"]]],
        ];
    }

    /**
     * @dataProvider dataProviderForTestEncode
     */
    public function testEncode(array $input, string $expected)
    {
        $this->assertSame($expected, Json::encode($input));
    }

    /**
     * testEncode のデータプロバイダ
     */
    public function dataProviderForTestEncode(): array
    {
        $altChar = "\u{fffd}";
        return [
            // 正常系
            [['key1' => 'value1', 'key2' => 'value2'], '{"key1":"value1","key2":"value2"}'],

            // 不正文字混入(\x80 - \xFF)
            [['key1' => "\x80"], "{\"key1\":\"{$altChar}\"}"],
            [['key1' => "value1", 'key2' => "\x80\xFF"], "{\"key1\":\"value1\",\"key2\":\"{$altChar}{$altChar}\"}"],
            [['key1' => ['nestKey1' => "value1\x80value1"]], "{\"key1\":{\"nestKey1\":\"value1{$altChar}value1\"}}"],
        ];
    }
}
