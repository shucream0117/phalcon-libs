<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\QueueManager;

use Enqueue\Redis\RedisConnectionFactory;

/**
 * php-enqueue でRedisを使ったキュー処理をする時に使う氏
 * @see https://php-enqueue.github.io/transport/redis/
 */
class RedisQueueManager extends AbstractQueueManager
{
    private string $host;
    private int $port;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    protected function getFactory(): RedisConnectionFactory
    {
        return new RedisConnectionFactory([
            'host' => $this->host,
            'port' => $this->port,
            'scheme_extensions' => ['phpredis'],
        ]);
    }
}
