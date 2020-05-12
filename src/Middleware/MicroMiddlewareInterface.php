<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Middleware;

use Phalcon\Events\Event;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * Microアプリケーションのライフサイクルに関するイベントのハンドラインターフェイス。
 * 実装クラス側では必要なものだけ実装すれば良い。
 *
 * @see https://docs.phalcon.io/4.0/en/events
 */
interface MicroMiddlewareInterface extends MiddlewareInterface
{
    public function beforeHandleRoute(Event $event, Micro $application);

    public function beforeExecuteRoute(Event $event, Micro $application);

    public function afterExecuteRoute(Event $event, Micro $application);

    public function afterHandleRoute(Event $event, Micro $application, ResponseInterface $returnedValue);

    public function afterBinding(Event $event, Micro $application);

    public function beforeNotFound(Event $event, Micro $application);
}
