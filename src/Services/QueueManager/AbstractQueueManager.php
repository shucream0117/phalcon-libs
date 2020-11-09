<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\QueueManager;

use Enqueue\Consumption\CallbackProcessor;
use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\QueueConsumer;
use Interop\Queue\Context;
use Interop\Queue\Exception;
use Interop\Queue\Exception\DeliveryDelayNotSupportedException;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Shucream0117\PhalconLib\Entities\AbstractQueueProperty;
use Shucream0117\PhalconLib\Services\AbstractService;

/**
 * php-enqueue の薄いラッパーです
 * @see https://php-enqueue.github.io/
 */
abstract class AbstractQueueManager extends AbstractService
{
    private ?Context $contextCache = null;

    /**
     * 普通のキューとして詰む
     *
     * @param string $queueName
     * @param AbstractQueueProperty $data
     * @throws DeliveryDelayNotSupportedException
     * @throws Exception
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     */
    public function enqueue(string $queueName, AbstractQueueProperty $data): void
    {
        $this->send($queueName, $data, null);
    }

    /**
     * 遅延実行キューとして詰む
     * (遅延実行は後続のキュー処理をブロックするため、即時実行キューと混在させないほうが良い)
     *
     * @param string $queueName
     * @param AbstractQueueProperty $data
     * @param int $delaySec
     * @throws DeliveryDelayNotSupportedException
     * @throws Exception
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     */
    public function enqueueDelayed(string $queueName, AbstractQueueProperty $data, int $delaySec): void
    {
        $this->send($queueName, $data, $delaySec);
    }

    /**
     * @param string $queueName
     * @param AbstractQueueProperty $data
     * @param int|null $delaySec
     * @throws DeliveryDelayNotSupportedException
     * @throws Exception
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     */
    private function send(string $queueName, AbstractQueueProperty $data, ?int $delaySec): void
    {
        $context = $this->getOrCreateContext();
        $queue = $context->createQueue($queueName);
        $producer = $context->createProducer();
        if (!is_null($delaySec)) {
            $producer->setDeliveryDelay($delaySec);
        }
        $producer->send(
            $queue,
            $context->createMessage('just get properties', $data->toArray())
        );
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
        $context = $this->getOrCreateContext();
        $queueConsumer = new QueueConsumer($context, $extensions);
        $queueConsumer->bind($queueName, $callbackProcessor);
        return $queueConsumer;
    }

    abstract protected function getContext(): Context;

    protected function getOrCreateContext(): Context
    {
        return $this->contextCache ?: $this->getContext();
    }
}
