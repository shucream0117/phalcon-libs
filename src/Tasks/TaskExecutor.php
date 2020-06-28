<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Tasks;

use Closure;
use InvalidArgumentException;
use Phalcon\Cli\Console;
use Throwable;

class TaskExecutor
{
    /**
     * @param Console $app
     * @param array $argv
     * @param Closure|null $onError function(Throwable $t):void な関数
     */
    public static function execute(Console $app, array $argv, ?\Closure $onError = null): void
    {
        if (count($argv) < 2) {
            throw new InvalidArgumentException('task name should be specified');
        }
        // actionはmainで固定する。
        $arguments = ['action' => 'main'];
        foreach ($argv as $k => $arg) {
            if ($k === 1) {
                $arguments['task'] = preg_replace('/Task$/i', '', $arg);
            } elseif ($k >= 2) {
                $arguments['params'][] = $arg;
            }
        }

        try {
            $app->handle($arguments);
        } catch (Throwable $throwable) {
            if ($onError) {
                $onError($throwable);
            }
        }
    }
}
