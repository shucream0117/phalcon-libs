<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Entities\JsonResponses;

use Shucream0117\PhalconLib\Validators\ValidationError;
use Shucream0117\PhalconLib\Validators\ValidationErrorDetail;

/**
 * バリデーションエラーレスポンス
 *
 * 【例】
 * {
 *   "error": {
 *      "code": 400000,
 *      "message": "Validation",
 *      "detail": [
 *          "field1" => [
 *              ['validator'=>'Alnum', 'message' => 'this is error message'],
 *              ['validator'=>'Email', 'message' => 'this is error message']
 *          ],
 *          "field2" => [
 *              ['validator'=>'Email', 'message' => 'this is error message']
 *          ],
 *      ]
 *   }
 * }
 */
class ValidationErrorResponse extends ErrorResponse
{
    protected array $error;

    /**
     * 通常のエラーメッセージ以外に入れたいものがあれば、$additionalDataに連想配列で指定出来る
     *
     * @param string|int $errorCode
     * @param string $message
     * @param ValidationError[] $validationErrors
     * @param array $additionalData
     */
    public function __construct($errorCode, string $message, array $validationErrors, array $additionalData = [])
    {
        parent::__construct($errorCode, $message, $additionalData);
        foreach ($validationErrors as $validationError) {
            $field = $validationError->getField();
            $this->error['detail'][$field] = array_map(function (ValidationErrorDetail $detail) {
                return [
                    'validator' => $detail->getValidatorName(),
                    'message' => $detail->getMessage(),
                ];
            }, $validationError->getDetails());
        }
    }
}
