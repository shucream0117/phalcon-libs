<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Middleware;

use Phalcon\Events\Event;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Micro;

class CorsMiddleware implements MicroMiddlewareInterface
{
    public function beforeHandleRoute(Event $event, Micro $application)
    {
        $origin = $application->request->getHeader('Origin') ?: '*';
        $application->response
            ->setHeader('Access-Control-Allow-Origin', $origin)
            ->setHeader(
                'Access-Control-Allow-Methods',
                'GET,PUT,POST,DELETE,OPTIONS'
            )
            ->setHeader(
                'Access-Control-Allow-Headers',
                'Origin, X-Requested-With, Content-Range, ' .
                'Content-Disposition, Content-Type, Authorization'
            )
            ->setHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function call(Micro $application)
    {
        return true;
    }

    public function beforeExecuteRoute(Event $event, Micro $application)
    {
        // do nothing
    }

    public function afterExecuteRoute(Event $event, Micro $application)
    {
        // do nothing
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
}
