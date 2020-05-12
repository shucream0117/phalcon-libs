<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Controllers;

use Phalcon\Mvc\Controller;

abstract class AbstractController extends Controller
{
    protected function getRemoteAddress(): string
    {
        $request = $this->request;
        if ($ip = $request->getServer('HTTP_X_FORWARDED_FOR')) {
            return $ip;
        }
        return $request->getServer('REMOTE_ADDR');
    }

    public function onConstruct()
    {
    }
}
