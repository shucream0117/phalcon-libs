<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Controllers;

use Shucream0117\PhalconLib\Utils\Http\JsonResponseTrait;

abstract class AbstractRestApiController extends AbstractController
{
    use JsonResponseTrait;

    /**
     * リクエストのJSONを取得
     * @return array
     */
    protected function getRequestJson(): array
    {
        return $this->request->getJsonRawBody(true);
    }
}
