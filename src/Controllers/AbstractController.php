<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Controllers;

use Phalcon\Mvc\Controller;
use Shucream0117\PhalconLib\Utils\Http\RequestUtil;

abstract class AbstractController extends Controller
{
    protected function getRemoteAddress(): string
    {
        return RequestUtil::getRemoteAddress($this->request);
    }

    public function onConstruct()
    {
    }
}
