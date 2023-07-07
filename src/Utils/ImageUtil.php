<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use Phalcon\Image\Adapter\AdapterInterface;
use Phalcon\Image\Enum;
use Shucream0117\PhalconLib\Constants\MimeType;

class ImageUtil
{
    /**
     * アスペクト比を保ったまま、長辺を$maxSizePxとしてリサイズする
     * @param AdapterInterface $image
     * @param int $maxSizePx
     * @return AdapterInterface
     */
    public static function resizeWithSameAspectRatio(AdapterInterface $image, int $maxSizePx): AdapterInterface
    {
        $width = $image->getWidth();
        $height = $image->getHeight();
        if ($maxSizePx < $width || $maxSizePx < $height) {
            $image = $image->resize($maxSizePx, $maxSizePx, (0 <= ($width - $height)) ? Enum::WIDTH : Enum::HEIGHT);
        }
        return $image;
    }

    /**
     * @param AdapterInterface $image
     * @param string $colorCode
     * @return AdapterInterface
     */
    public static function fillColor(AdapterInterface $image, string $colorCode = '#ffffff'): AdapterInterface
    {
        // pngの場合の透過部分白埋め
        if (MimeType::isEqual($image->getMime(), MimeType::PNG)) {
            $image->background($colorCode);
        }
        return $image;
    }

    /**
     * 短辺の長さで中央から正方形に切り抜く
     * @param AdapterInterface $image
     * @return AdapterInterface
     */
    public static function cropSquare(AdapterInterface $image): AdapterInterface
    {
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();

        if ($imageWidth === $imageHeight) {
            return $image;
        }
        if ($imageWidth < $imageHeight) { // 縦長
            $length = $imageWidth;
            $offsetX = 0;
            $offsetY = ($imageHeight - $imageWidth) / 2;
        } else { // 横長
            $length = $imageHeight;
            $offsetX = ($imageWidth - $imageHeight) / 2;
            $offsetY = 0;
        }
        $image->crop($length, $length, (int)round($offsetX), (int)round($offsetY));
        return $image;
    }
}
