<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Tasks;

use Enqueue\Consumption\CallbackProcessor;
use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\Extension\SignalExtension;
use Exception;
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
}
