<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use Phalcon\Logger\Item;

class CustomJsonFormatter extends \Phalcon\Logger\Formatter\Json
{
    public function format(Item $item): string
    {
        // 一旦親クラスのメソッドで処理を行い、Json文字列を配列に戻してから再度Json文字列にする。無駄が多いが...
        $tmp = Json::decode(parent::format($item));
        $tmp['context'] = $item->getContext(); // contextを追加
        return Json::encode($tmp);
    }
}
