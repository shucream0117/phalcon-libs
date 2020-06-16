<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use Phalcon\Image\Adapter\AbstractAdapter;
use Phalcon\Image\Enum;

class ImageUtil
{
    /**
     * アスペクト比を保ったまま、長辺を$maxSizePxとしてリサイズする
     * @param AbstractAdapter $image
     * @param int $maxSizePx
     * @return AbstractAdapter
     */
    public static function resizeWithSameAspectRatio(AbstractAdapter $image, int $maxSizePx): AbstractAdapter
    {
        $width = $image->getWidth();
        $height = $image->getHeight();
        if ($maxSizePx < $width || $maxSizePx < $height) {
            $image->resize($maxSizePx, $maxSizePx, (0 <= ($width - $height)) ? Enum::WIDTH : Enum::HEIGHT);
        }
        return $image;
    }
}
