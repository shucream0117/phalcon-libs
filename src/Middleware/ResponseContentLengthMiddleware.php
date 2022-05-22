<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Middleware;

use Phalcon\Events\Event;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Micro;

class ResponseContentLengthMiddleware implements MicroMiddlewareInterface
{
    public function beforeHandleRoute(Event $event, Micro $application)
    {
        // do nothing
    }

    public function beforeExecuteRoute(Event $event, Micro $application)
    {
        // do nothing
    }

    public function afterExecuteRoute(Event $event, Micro $application)
    {
        if ($application->response->hasHeader('Content-Length')) {
            return true;
        }
        $contentLength = strlen($application->response->getContent());
        $application->response->setContentLength($contentLength);
        return true;
    }

    public function afterHandleRoute(Event $event, Micro $application, ResponseInterface $returnedValue)
    {
        // do nothing
    }

    public function afterBinding(Event $event, Micro $application)
    {
        // do nothing
    }

    public function beforeNotFound(Event $event, Micro $application)
    {
        // do nothing
    }

    public function call(\Phalcon\Mvc\Micro $application)
    {
        return true;
    }
}
