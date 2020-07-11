<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

// 画像や動画の縦横サイズを扱う時に便利なクラス
class Dimensions
{
    private int $width;
    private int $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function swap(): self
    {
        $w = $this->getWidth();
        $h = $this->getHeight();
        $this->width = $h;
        $this->height = $w;
        return $this;
    }

    /**
     * 長辺を $longSideLength に合わせて、アスペクト比を保ったままリサイズする。
     * 長辺が $longSideLength を下回る場合はそのまま。
     * @param int $longSideLength
     * @return $this
     */
    public function adjustSize(int $longSideLength): self
    {
        $currentHeight = $this->getHeight();
        $currentWidth = $this->getWidth();

        if (($currentWidth <= $longSideLength) && ($currentHeight <= $longSideLength)) {
            // 長辺が規定値を下回る場合は、そのままのサイズにする
            return $this;
        }

        // 縦横のどちらかが規定値を上回る場合はアスペクト比を保ってリサイズする
        if ($currentWidth < $currentHeight) { // 縦長の場合
            $newHeight = $longSideLength;
            $newWidth = (int)round(($currentWidth * $newHeight) / $currentHeight);
        } else { // 横長 or 正方形の場合
            $newWidth = $longSideLength;
            $newHeight = (int)round(($currentHeight * $newWidth) / $currentWidth);
        }
        $this->height = $newHeight;
        $this->width = $newWidth;
        return $this;
    }
}
