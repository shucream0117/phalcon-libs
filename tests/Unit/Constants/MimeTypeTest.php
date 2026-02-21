<?php

namespace Tests\Unit\Constants;

use Shucream0117\PhalconLib\Constants\MimeType;
use Tests\Unit\TestBase;

class MimeTypeTest extends TestBase
{
    /**
     * @dataProvider dataProviderForTestMimeToExtension
     */
    public function testMimeToExtension(string $mimeType, string $expected): void
    {
        $this->assertSame($expected, MimeType::mimeToExtension($mimeType));
    }

    public function dataProviderForTestMimeToExtension(): array
    {
        return [
            [MimeType::JPEG, 'jpg'],
            [MimeType::PNG, 'png'],
            [MimeType::GIF, 'gif'],
            [MimeType::WEBP, 'webp'],
            [MimeType::SVG, 'svg'],
            [MimeType::BMP, 'bmp'],
            [MimeType::MP4, 'mp4'],
            [MimeType::MOV, 'mov'],
            [MimeType::WEBM, 'webm'],
            [MimeType::JSON, 'json'],
        ];
    }

    /**
     * @dataProvider dataProviderForTestExtensionToMime
     */
    public function testExtensionToMime(string $ext, string $expected): void
    {
        $this->assertSame($expected, MimeType::extensionToMime($ext));
    }

    public function dataProviderForTestExtensionToMime(): array
    {
        return [
            ['jpg', MimeType::JPEG],
            ['jpeg', MimeType::JPEG],
            ['png', MimeType::PNG],
            ['gif', MimeType::GIF],
            ['webp', MimeType::WEBP],
            ['svg', MimeType::SVG],
            ['bmp', MimeType::BMP],
            ['mp4', MimeType::MP4],
            ['mov', MimeType::MOV],
            ['webm', MimeType::WEBM],
            ['json', MimeType::JSON],

            // 大文字もチェック
            ['JPG', MimeType::JPEG],
            ['JPEG', MimeType::JPEG],
            ['PNG', MimeType::PNG],
            ['GIF', MimeType::GIF],
            ['WEBP', MimeType::WEBP],
            ['SVG', MimeType::SVG],
            ['BMP', MimeType::BMP],
            ['MP4', MimeType::MP4],
            ['MOV', MimeType::MOV],
            ['WEBM', MimeType::WEBM],
            ['JSON', MimeType::JSON],
        ];
    }
}
