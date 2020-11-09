<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils\Http;

use JsonSerializable;
use Phalcon\Http\Response;
use Phalcon\Http\Response\Headers;
use Phalcon\Http\Response\HeadersInterface;
use Phalcon\Http\ResponseInterface;
use Shucream0117\PhalconLib\Entities\JsonResponses\ErrorResponse;
use Shucream0117\PhalconLib\Entities\JsonResponses\AbstractResponseBody;
use Symfony\Component\HttpFoundation\Response as StatusCode;

trait JsonResponseTrait
{
    protected static int $JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR;

    /*
     * 20x
     */

    public function ok(?AbstractResponseBody $content = null): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_OK, $content);
    }

    public function created(?AbstractResponseBody $content = null): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_CREATED, $content);
    }

    public function noContent(): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_NO_CONTENT, null);
    }

    /*
     * 30x
     */

    public function movedPermanently(string $to): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_MOVED_PERMANENTLY, null, (new Headers())->set('Location', $to));
    }

    public function found(string $to): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_FOUND, null, (new Headers())->set('Location', $to));
    }

    public function seeOther(string $to): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_SEE_OTHER, null, (new Headers())->set('Location', $to));
    }


    /*
     * 40x
     */

    public function badRequest(ErrorResponse $content): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_BAD_REQUEST, $content);
    }

    public function unauthorized(ErrorResponse $content): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_UNAUTHORIZED, $content);
    }

    public function forbidden(ErrorResponse $content): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_FORBIDDEN, $content);
    }

    public function notFound(ErrorResponse $content): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_NOT_FOUND, $content);
    }

    public function methodNotAllowed(ErrorResponse $content): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_METHOD_NOT_ALLOWED, $content);
    }

    /*
     * 50x
     */

    public function internalServerError(ErrorResponse $content): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_INTERNAL_SERVER_ERROR, $content);
    }

    public function serviceUnavailable(ErrorResponse $content): ResponseInterface
    {
        return $this->createResponse(StatusCode::HTTP_SERVICE_UNAVAILABLE, $content);
    }

    protected function createResponse(
        int $statusCode,
        ?JsonSerializable $content = null,
        ?HeadersInterface $headers = null
    ): ResponseInterface {
        $response = $this->response ?? new Response();
        $response->setStatusCode($statusCode);
        if (is_null($content)) {
            $content = new \stdClass();
        }
        if ($headers) {
            $response->setHeaders($headers);
        }
        $response->setJsonContent($content, static::$JSON_OPTIONS);
        return $response;
    }

    /**
     * ステータスコードを与えてレスポンス返却メソッドをコールする。
     * index.phpでcatchしたあとに使うぐらいで、基本的にはあまり使わないこと。
     *
     * @param int $statusCode
     * @param ErrorResponse $content
     * @return ResponseInterface
     */
    public function getErrorResponseByStatusCode(int $statusCode, ErrorResponse $content): ResponseInterface
    {
        $map = [
            // 40x
            StatusCode::HTTP_BAD_REQUEST => fn() => $this->badRequest($content),
            StatusCode::HTTP_UNAUTHORIZED => fn() => $this->unauthorized($content),
            StatusCode::HTTP_FORBIDDEN => fn() => $this->forbidden($content),
            StatusCode::HTTP_NOT_FOUND => fn() => $this->notFound($content),
            StatusCode::HTTP_METHOD_NOT_ALLOWED => fn() => $this->methodNotAllowed($content),

            // 50x
            StatusCode::HTTP_INTERNAL_SERVER_ERROR => fn() => $this->internalServerError($content),
            StatusCode::HTTP_SERVICE_UNAVAILABLE => fn() => $this->serviceUnavailable($content),
        ];
        if ($func = ($map[$statusCode] ?? null)) {
            return $func();
        }
        // この場合はとりあえず500で返しておく。例外にしてしまうとそれはそれで使う側で困りそうなので...
        return $this->internalServerError($content);
    }
}
