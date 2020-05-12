<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\JsonResponses;

/**
 * エラーレスポンス
 *
 * 【例】
 * {
 *   "error": {
 *      "code": 404,
 *      "message": "Not Found"
 *   }
 * }
 */
class ErrorResponse extends AbstractResponseBody
{
    protected array $error;

    /**
     * 通常のエラーメッセージ以外に入れたいものがあれば、$additionalDataに連想配列で指定出来る
     *
     * @param string|int $errorCode
     * @param string $message
     * @param array $additionalData
     */
    public function __construct($errorCode, string $message, array $additionalData = [])
    {
        $this->error = array_merge(['code' => $errorCode, 'message' => $message], $additionalData);
    }
}
