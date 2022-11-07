<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Tasks;

use Enqueue\Consumption\CallbackProcessor;
use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\Extension\SignalExtension;
use Exception;
use Interop\Queue\Exception\DeliveryDelayNotSupportedException;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Processor;
use Shucream0117\PhalconLib\Entities\AbstractQueueProperty;
use Shucream0117\PhalconLib\Services\QueueManager\AbstractQueueManager;

abstract class AbstractQueueProcessorTask extends AbstractTask
{
    abstract protected function getCallbackProcessor(): CallbackProcessor;

    abstract protected function getQueueName(): string;

    abstract protected function getQueueManager(): AbstractQueueManager;

    public function run(): void
    {
        $this->processQueue($this->getCallbackProcessor());
    }

    /**
     * @param CallbackProcessor $processor
     * @throws Exception
     */
    protected function processQueue(CallbackProcessor $processor): void
    {
        $consumer = $this->getQueueManager()->bindCallback(
            $this->getQueueName(),
            $processor,
            $this->getExtensions()
        );
        $consumer->consume();
    }

    protected function getExtensions(): ChainExtension
    {
        return new ChainExtension([
            new SignalExtension(),
        ]);
    }

    /**
     * 遅延秒数(ミリ秒) を指定して積み直す。
     *
     * @param AbstractQueueProperty $queueProperty
     * @param int $delayMilliSec
     * @return string
     * @throws \Interop\Queue\Exception
     * @throws DeliveryDelayNotSupportedException
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     */
    protected function requeueWithDelay(AbstractQueueProperty $queueProperty, int $delayMilliSec): string
    {
        $this->getQueueManager()->enqueueDelayed($this->getQueueName(), $queueProperty, $delayMilliSec);
        return Processor::REJECT;
    }
}
