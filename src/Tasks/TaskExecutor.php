<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Tasks;

use Closure;
use Phalcon\Cli\Console;
use Throwable;

/*
 * Taskを起動するためのクラス。
 * executor.php のようなファイルを作成し、その中でConsoleクラスのインスタンスを初期化して、
 * TaskExecutor::execute の引数に渡す。
 *
 * 例えばTasks/UserTaskを起動する場合、
 * php app/Tasks/executor.php --task=UserTask --hoge=fuga というコマンドを発行する。
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
     * @param Closure|null $onError function(Throwable $t):void な関数
     */
    public static function execute(Console $app, ?\Closure $onError = null): void
    {
        $options = getopt('', ['task:']);
        if (!$taskName = ($options['task'] ?? null)) {
            throw new \Exception('option --task is required');
        }
        try {
            $app->handle([
                'task' => preg_replace('/Task$/i', '', $taskName),
                'action' => 'main', // actionはmainで固定する。
            ]);
        } catch (Throwable $throwable) {
            if ($onError) {
                $onError($throwable);
            }
        }
    }
}
