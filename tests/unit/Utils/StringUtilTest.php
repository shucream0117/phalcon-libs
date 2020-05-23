<?php

namespace Tests\Unit\Constants;

use Shucream0117\PhalconLib\Utils\StringUtil;
use Tests\Unit\TestBase;

class StringUtilTest extends TestBase
{
    /**
     * @covers       StringUtil::removeEmoji
     * @param string $input
     * @param string $expected
     * @dataProvider dataProviderForTestRemoveEmoji
     */
    public function testRemoveEmoji(string $input, string $expected)
    {
        $this->assertSame($expected, StringUtil::removeEmoji($input));
    }

    public function dataProviderForTestRemoveEmoji(): array
    {
        return [
            ['test', 'test'],
            ['1234', '1234'],
            ['てすと', 'てすと'],
            ['テスト', 'テスト'],
            ['検証', '検証'],
            ['test1234てすと検証', 'test1234てすと検証'],
            ['☔', ''],
            ['こんにちは🍕', 'こんにちは'],
            ['こんに🍕ちは', 'こんにちは'],
            ['🍕こん🍣にちは🍺', 'こんにちは'],
        ];
    }
}
