<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use Phalcon\Db\Adapter\Pdo\AbstractPdo;

/**
 * 常時起動系のTaskで長時間クエリを発行しないためにコネクションが切断されることがあり、
 * その場合に再接続を行うためのTrait
 */
trait DbReconnectorTrait
{
    protected function isMysqlGoneAway(\PDOException $e): bool
    {
        return preg_match("/gone\saway/i", $e->getMessage()) === 1;
    }

    protected function reconnectDb(AbstractPdo $db): void
    {
        $db->close();
        $db->connect();
    }
}
