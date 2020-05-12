<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Repositories;

use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\ModelInterface;

abstract class AbstractRepository
{
    protected function generateDatabaseErrorMessageFromModel(ModelInterface $model, string $delimiter = ','): string
    {
        $messageStrList = array_map(fn(MessageInterface $m) => $m->getMessage(), $model->getMessages());
        return implode($delimiter, $messageStrList);
    }
}
