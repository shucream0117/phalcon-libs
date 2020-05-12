<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Controllers;

use Shucream0117\PhalconLib\Utils\Http\JsonResponseTrait;

abstract class AbstractRestApiController extends AbstractController
{
    use JsonResponseTrait;
}
