<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Tasks;

use Closure;
use InvalidArgumentException;
use Phalcon\Cli\Console;
use Throwable;

/*
 * Taskを起動するためのクラス。
 * executor.php のようなファイルを作成し、その中でConsoleクラスのインスタンスを初期化して、
 * TaskExecutor::execute の引数に渡す。
 *
 * 例えばTasks/UserTaskを起動する場合、
 * php app/Tasks/executor.php UserTask arg1 arg2... というコマンドを発行する。
 *
 * Tasks/QueueProcessors/UserTask のようにTasks下で更にディレクトリ配下にあるクラスを実行する場合は、
 * php app/Tasks/executor.php QueueProcessors\\UserTask とする。
 *
 * タスク名の指定は、 User または UserTask どちらでも有効。
 * 実行するTaskクラスは全てAbstractTaskのサブクラスである必要がある。
 *
 * https://docs.phalcon.io/4.0/ja-jp/application-cli
 * PhalconのCliアプリケーションのドキュメントをベースに実装したが、
 * actionをmainに固定するなど、多少シンプルになるように改変しているので注意。
 *
 */
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
