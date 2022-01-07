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
        $application->response
            ->setHeader('Access-Control-Allow-Origin', $this->getAllowOrigin($application))
            ->setHeader(
                'Access-Control-Allow-Methods',
                implode(',', $this->getAllowMethods($application))
            )
            ->setHeader('Access-Control-Allow-Headers', implode(',', $this->getAllowHeaders($application)))
            ->setHeader('Access-Control-Max-Age', $this->getMaxAge())
            ->setHeader('Access-Control-Allow-Credentials', 'true');
        return true;
    }

    protected function getAllowHeaders(Micro $application): array
    {
        return ['Origin', 'X-Requested-With', 'Content-Type', 'Authorization'];
    }

    protected function getAllowOrigin(Micro $application): string
    {
        return $application->request->getHeader('Origin') ?: '*';
    }

    protected function getAllowMethods(Micro $application): array
    {
        return ['GET', 'PUT', 'POST', 'DELETE', 'OPTIONS'];
    }

    protected function getMaxAge(): int
    {
        return 86400;
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
