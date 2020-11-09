<?php

namespace Tests\Unit\Utils;

use Shucream0117\PhalconLib\Utils\Dimensions;
use Tests\Unit\TestBase;

class DimensionsTest extends TestBase
{
    /**
     * @covers Dimensions::swap
     */
    public function testSwap()
    {
        $dimensions = new Dimensions(100, 200);
        $this->assertSame(100, $dimensions->getWidth());
        $this->assertSame(200, $dimensions->getHeight());
    }

    /**
     * @dataProvider dataProviderForTestResizeWithSameAspectRatio
     * @param int $height
     * @param int $width
     * @param int $longSideMaxLengthPx
     * @param array $expected
     */
    public function testResizeWithSameAspectRatio(int $height, int $width, int $longSideMaxLengthPx, array $expected)
    {
        $dimensions = new Dimensions($width, $height);

        $resized = $dimensions->resizeWithSameAspectRatio($longSideMaxLengthPx);
        $this->assertSame($expected['height'], $resized->getHeight());
        $this->assertSame($expected['width'], $resized->getWidth());
    }

    /**
     * testAdjustSize のバリデーター
     * @return array
     */
    public function dataProviderForTestResizeWithSameAspectRatio(): array
    {
        return [
            [1920, 1080, 1920, ['height' => 1920, 'width' => 1080]],
            [1080, 1920, 1920, ['height' => 1080, 'width' => 1920]],

            [1920, 1080, 16, ['height' => 16, 'width' => 9]],
            [1920, 1080, 480, ['height' => 480, 'width' => 270]],
            [1920, 1080, 640, ['height' => 640, 'width' => 360]],
            [1920, 1080, 1440, ['height' => 1440, 'width' => 810]],
            [1920, 1080, 1568, ['height' => 1568, 'width' => 882]],
            [1952, 1098, 1920, ['height' => 1920, 'width' => 1080]],
            [2144, 1206, 1920, ['height' => 1920, 'width' => 1080]],

            [1080, 1920, 16, ['height' => 9, 'width' => 16]],
            [1080, 1920, 480, ['height' => 270, 'width' => 480]],
            [1080, 1920, 640, ['height' => 360, 'width' => 640]],
            [1080, 1920, 1440, ['height' => 810, 'width' => 1440]],
            [1080, 1920, 1568, ['height' => 882, 'width' => 1568]],
            [1098, 1952, 1920, ['height' => 1080, 'width' => 1920]],
            [1206, 2144, 1920, ['height' => 1080, 'width' => 1920]],
        ];
    }
}
