<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use Phalcon\Logger\Item;

class CustomJsonFormatter extends \Phalcon\Logger\Formatter\Json
{
    public function format(Item $item): string
    {
        /*
         * context に単一のキーしか存在しない場合で、そのキーに対応する値が配列の場合に
         * Array to string conversion の Notice が出る問題があるため、フォーマットを処理を自前で実装
         * ついでにデフォルトのフォーマッターが出力してくれない context の内容も出力する。
         */
        $data = [
            'type' => $item->getType(),
            'message' => $item->getMessage(),
            'timestamp' => $this->getFormattedDate(), // "2024-04-05T15:02:56+00:00" のような形式
            'context' => $item->getContext(),
        ];
        return Json::encode($data);
    }
}
