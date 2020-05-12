<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils\Http;

use Phalcon\Http\RequestInterface;

class RequestUtil
{
    public static function getRemoteAddress(RequestInterface $request): string
    {
        if ($ip = $request->getServer('HTTP_X_FORWARDED_FOR')) {
            return $ip;
        }
        return $request->getServer('REMOTE_ADDR');
    }
}
