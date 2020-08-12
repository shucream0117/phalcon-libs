<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Tasks;

use Phalcon\Cli\Task;

abstract class AbstractTask extends Task
{
    abstract protected function run(): void;

    final public function mainAction(): void
    {
        $this->run();
    }

    protected function getParams(): array
    {
        return $this->dispatcher->getParams();
    }
}
