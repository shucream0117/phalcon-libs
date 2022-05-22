<?php

namespace Tests\Unit\Middleware;

use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro;
use Shucream0117\PhalconLib\Constants\EventType;
use Shucream0117\PhalconLib\Middleware\ResponseContentLengthMiddleware;
use Tests\Unit\TestBase;

class ResponseContentLengthMiddlewareTest extends TestBase
{
    public function testResponseContentLengthMiddleware()
    {
        $app = new Micro();
        $app->get(
            '/test',
            function () use ($app) {
                return $app->response->setContent('ok');
            }
        );
        $manager = new Manager();
        $manager->attach(
            EventType::MICRO,
            new ResponseContentLengthMiddleware()
        );
        $app->setEventsManager($manager);

        ob_start();
        $response = $app->handle('/test');
        ob_end_clean();

        $this->assertSame('ok', $response->getContent());
        $this->assertSame('2', $response->getHeaders()->get('Content-Length'));
    }
}
