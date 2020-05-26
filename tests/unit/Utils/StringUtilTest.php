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

    /**
     * @param string $input
     * @param string $expected
     * @dataProvider dataProviderForTestRemovePlusSectionFromEmail
     */
    public function testRemovePlusSectionFromEmail(string $input, string $expected)
    {
        $this->assertSame($expected, StringUtil::removePlusSectionFromEmail($input));
    }

    public function dataProviderForTestRemovePlusSectionFromEmail(): array
    {
        return [
            ['shucream117@gmail.com', 'shucream117@gmail.com'],
            ['shucream.117@gmail.com', 'shucream.117@gmail.com'],
            ['shucream.117+hoge@gmail.com', 'shucream.117@gmail.com'],
            ['shucream117+hoge@gmail.com', 'shucream117@gmail.com'],
            ['shucream117+hoge+fuga@gmail.com', 'shucream117@gmail.com'],
            ['shucream117+hoge+fuga+piyo@gmail.com', 'shucream117@gmail.com'],
            ['shucream117+ほげ@gmail.com', 'shucream117@gmail.com'],
            ['shucream117+@gmail.com', 'shucream117@gmail.com'],
        ];
    }
}
