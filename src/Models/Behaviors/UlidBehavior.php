<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Models\Behaviors;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\ModelInterface;
use Ulid\Ulid;

class UlidBehavior extends Behavior
{
    public function notify(string $eventType, ModelInterface $model)
    {
        if ($eventType === 'beforeValidationOnCreate') {
            $idFieldName = UlidTrait::$COLUMN_ID;
            if (!isset($model->{$idFieldName})) {
                $model->assign([UlidTrait::$COLUMN_ID => Ulid::generate()->__toString()]);
            }
        }
    }
}
