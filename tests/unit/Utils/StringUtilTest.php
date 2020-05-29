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
            ['†œ∑´¥¨ˆøπåß∂ƒ˙∆˚¬Ω≈ç√∫˜µ', '†œ∑´¥¨ˆøπåß∂ƒ˙∆˚¬Ω≈ç√∫˜µ'],
            ['~!@#$%^&*()_+=-[]{}\|":;<>,./?', '~!@#$%^&*()_+=-[]{}\|":;<>,./?'],
            ['①', '①'],
            ['®', ''],
            ['©', ''],
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

    /**
     * @covers       StringUtil::hasEmoji
     * @param string $input
     * @param bool $expected
     * @dataProvider dataProviderForTestHasEmoji
     */
    public function testHasEmoji(string $input, bool $expected)
    {
        $this->assertSame($expected, StringUtil::hasEmoji($input));
    }

    public function dataProviderForTestHasEmoji(): array
    {
        return [
            ['test', false],
            ['1234', false],
            ['てすと', false],
            ['テスト', false],
            ['検証', false],
            ['test1234てすと検証', false],
            ['†œ∑´¥¨ˆøπåß∂ƒ˙∆˚¬Ω≈ç√∫˜µ', false],
            ['~!@#$%^&*()_+=-[]{}\|":;<>,./?', false],
            ['①', false],
            ['®', true],
            ['©', true],
            ['☔', true],
            ['こんにちは🍕', true],
            ['こんに🍕ちは', true],
            ['🍕こん🍣にちは🍺', true],
        ];
    }
}
