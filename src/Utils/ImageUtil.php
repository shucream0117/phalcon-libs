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
     * TODO 動作確認
     * アスペクト比を保ったまま横幅を $widthPx にリサイズする
     * @param AdapterInterface $image
     * @param int $width
     * @return AdapterInterface
     */
    public static function resizeByWidth(
        AdapterInterface $image,
        int $widthPx,
        bool $avoidEnlarge = false
    ): AdapterInterface {
        if ($avoidEnlarge && $image->getWidth() <= $widthPx) {
            return $image;
        }
        return $image->resize($widthPx, null, Enum::WIDTH);
    }

    /**
     * TODO 動作確認
     * アスペクト比を保ったまま縦幅を $heightPx にリサイズする
     * @param AdapterInterface $image
     * @param int $heightPx
     * @return AdapterInterface
     */
    public static function resizeByHeight(
        AdapterInterface $image,
        int $heightPx,
        bool $avoidEnlarge = false
    ): AdapterInterface {
        if ($avoidEnlarge && $image->getHeight() <= $heightPx) {
            return $image;
        }
        return $image->resize(null, $heightPx, Enum::HEIGHT);
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

    /**
     * TODO 動作確認
     * 高さを指定し、横幅はそのままで中央から切り抜く
     * @param AdapterInterface $image
     * @param int $heightPx
     * @return AdapterInterface
     */
    public static function cropByHeight(AdapterInterface $image, int $heightPx): AdapterInterface
    {
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();
        if ($imageHeight <= $heightPx) {
            return $image;
        }
        $offsetY = ($imageHeight - $heightPx) / 2;
        $image->crop($imageWidth, $heightPx, 0, (int)round($offsetY));
        return $image;
    }

    /**
     * TODO 動作確認
     * 横幅を指定し、高さはそのままで中央から切り抜く
     * @param AdapterInterface $image
     * @param int $widthPx
     * @return AdapterInterface
     */
    public static function cropByWidth(AdapterInterface $image, int $widthPx): AdapterInterface
    {
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();
        if ($imageWidth <= $widthPx) {
            return $image;
        }
        $offsetX = ($imageWidth - $widthPx) / 2;
        $image->crop($widthPx, $imageHeight, (int)round($offsetX), 0);
        return $image;
    }
}
