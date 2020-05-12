<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Middleware;

use Phalcon\Events\Event;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Micro;
use Shucream0117\PhalconLib\Entities\JsonResponses\ErrorResponse;
use Shucream0117\PhalconLib\Utils\Http\JsonResponseTrait;

class NotFoundMiddleware implements MicroMiddlewareInterface
{
    use JsonResponseTrait;

    protected ErrorResponse $notFoundErrorResponse;

    public function __construct(ErrorResponse $notFoundErrorResponse)
    {
        $this->notFoundErrorResponse = $notFoundErrorResponse;
    }

    /*
     * ここでエラーレスポンスを返却し、処理を止める。
     */
    public function beforeNotFound(Event $event, Micro $application)
    {
        $this->notFound($this->notFoundErrorResponse)->send();
        return false;
    }

    public function call(Micro $application)
    {
        return true;
    }

    final public function beforeHandleRoute(Event $event, Micro $application)
    {
        // do nothing
    }

    final public function beforeExecuteRoute(Event $event, Micro $application)
    {
        // do nothing
    }

    final public function afterExecuteRoute(Event $event, Micro $application)
    {
        // do nothing
    }

    final public function afterHandleRoute(Event $event, Micro $application, ResponseInterface $returnedValue)
    {
        // do nothing
    }

    final public function afterBinding(Event $event, Micro $application)
    {
        // do nothing
    }
}
