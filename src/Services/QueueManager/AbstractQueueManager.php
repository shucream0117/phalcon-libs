<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\QueueManager;

use Enqueue\Consumption\CallbackProcessor;
use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\QueueConsumer;
use Interop\Queue\ConnectionFactory;
use Shucream0117\PhalconLib\Services\AbstractService;

/**
 * php-enqueue の薄いラッパーです
 * @see https://php-enqueue.github.io/
 */
abstract class AbstractQueueManager extends AbstractService
{
    private ?ConnectionFactory $factoryCache = null;

    public function enqueue(string $queueName, array $data): void
    {
        $context = $this->getOrCreateFactory()->createContext();
        $queue = $context->createQueue($queueName);
        $context->createProducer()->send($queue, $context->createMessage('just get properties', $data));
    }

    /**
     * @param string $queueName
     * @param CallbackProcessor $callbackProcessor
     * @param ChainExtension|null $extensions
     * @return QueueConsumer
     */
    public function bindCallback(
        string $queueName,
        CallbackProcessor $callbackProcessor,
        ?ChainExtension $extensions = null
    ): QueueConsumer {
        $context = $this->getOrCreateFactory()->createContext();
        $queueConsumer = new QueueConsumer($context, $extensions);
        $queueConsumer->bind($queueName, $callbackProcessor);
        return $queueConsumer;
    }

    abstract protected function getFactory(): ConnectionFactory;

    protected function getOrCreateFactory(): ConnectionFactory
    {
        return $this->factoryCache ?: $this->getFactory();
    }
}
