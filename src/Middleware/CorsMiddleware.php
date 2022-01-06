<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Middleware;

use Phalcon\Events\Event;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Micro;

class CorsMiddleware implements MicroMiddlewareInterface
{
    public function beforeHandleRoute(Event $event, Micro $application)
    {
        $request = $application->request;
        $application->response
            ->setHeader('Access-Control-Allow-Origin', $this->getAllowOrigin($request))
            ->setHeader(
                'Access-Control-Allow-Methods',
                implode(',', $this->getAllowMethods($request))
            )
            ->setHeader('Access-Control-Allow-Headers', implode(',', $this->getAllowHeaders($request)))
            ->setHeader('Access-Control-Allow-Credentials', 'true');
        return true;
    }

    protected function getAllowHeaders(RequestInterface $request): array
    {
        return ['Origin', 'X-Requested-With', 'Content-Type', 'Authorization'];
    }

    protected function getAllowOrigin(RequestInterface $request): string
    {
        return $request->getHeader('Origin') ?: '*';
    }

    protected function getAllowMethods(RequestInterface $request): array
    {
        return ['GET', 'PUT', 'POST', 'DELETE', 'OPTIONS'];
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
