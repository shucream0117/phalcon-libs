<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services\Aws;

use Aws\Result;
use Aws\S3\S3Client;
use Exception;

class S3
{
    const STORAGE_CLASS_STANDARD = 'STANDARD'; // 標準
    const STORAGE_CLASS_STANDARD_IA = 'STANDARD_IA'; // 標準低頻度
    const STORAGE_CLASS_ONEZONE_IA = 'ONEZONE_IA'; // 1ゾーン低頻度
    const STORAGE_CLASS_INTELLIGENT_TIERING = 'INTELLIGENT_TIERING'; // 30日間アクセスがないときは低頻度になる賢いやつ

    const ACL_PRIVATE = 'private';

    private S3Client $client;

    public function __construct(S3Client $client)
    {
        $this->client = $client;
    }

    /**
     * S3に保存する
     *
     * @param string|null $data
     * @param string|null $sourceFilePath
     * @param string $bucket
     * @param string $filePath
     * @param string $contentType
     * @param string|null $acl
     * @param string $storageClass
     * @return Result
     * @throws Exception
     */
    private function saveToS3(
        ?string $data,
        ?string $sourceFilePath,
        string $bucket,
        string $filePath,
        string $contentType,
        string $acl,
        string $storageClass
    ): Result {
        if (!$data && !$sourceFilePath) {
            throw new Exception('data or sourceFilePath is required');
        }
        if ($data && $sourceFilePath) {
            throw new Exception('data and sourceFilePath are exclusive');
        }
        $params = [
            'Bucket' => $bucket,
            'Key' => $filePath,
            'ACL' => $acl,
            'ContentType' => $contentType,
            'StorageClass' => $storageClass,
        ];
        if ($data) {
            $params['Body'] = $data;
        }
        if ($sourceFilePath) {
            $params['SourceFile'] = $sourceFilePath;
        }
        return $this->client->putObject($params);
    }

    /**
     * ローカルのファイルパスを指定してS3に保存する
     *
     * @param string $sourceFilePath
     * @param string $bucket
     * @param string $destinationFilePath
     * @param string $contentType
     * @param string|null $acl
     * @return Result
     * @throws Exception
     */
    public function saveToS3StorageClassStandardFromFilePath(
        string $sourceFilePath,
        string $bucket,
        string $destinationFilePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): Result {
        return $this->saveToS3(null, $sourceFilePath, $bucket, $destinationFilePath, $contentType, $acl, static::STORAGE_CLASS_STANDARD);
    }

    /**
     * ローカルのファイルパスを指定してS3に保存する
     * @param string $sourceFilePath
     * @param string $bucket
     * @param string $destinationFilePath
     * @param string $contentType
     * @param string|null $acl
     * @return Result
     * @throws Exception
     */
    public function saveToS3StorageClassStandardIAFromFilePath(
        string $sourceFilePath,
        string $bucket,
        string $destinationFilePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): Result {
        return $this->saveToS3(null, $sourceFilePath, $bucket, $destinationFilePath, $contentType, $acl, static::STORAGE_CLASS_STANDARD_IA);
    }


    /**
     * ローカルのファイルパスを指定してS3に保存する
     * @param string $sourceFilePath
     * @param string $bucket
     * @param string $destinationFilePath
     * @param string $contentType
     * @param string|null $acl
     * @return Result
     * @throws Exception
     */
    public function saveToS3StorageClassOneZoneIAFromFilePath(
        string $sourceFilePath,
        string $bucket,
        string $destinationFilePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): Result {
        return $this->saveToS3(null, $sourceFilePath, $bucket, $destinationFilePath, $contentType, $acl, static::STORAGE_CLASS_ONEZONE_IA);
    }

    /**
     * ローカルのファイルパスを指定してS3に保存する
     * @param string $sourceFilePath
     * @param string $bucket
     * @param string $destinationFilePath
     * @param string $contentType
     * @param string|null $acl
     * @return Result
     * @throws Exception
     */
    public function saveToS3StorageClassIntelligentTieringFromFilePath(
        string $sourceFilePath,
        string $bucket,
        string $destinationFilePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): Result {
        return $this->saveToS3(null, $sourceFilePath, $bucket, $destinationFilePath, $contentType, $acl, static::STORAGE_CLASS_INTELLIGENT_TIERING);
    }

    /**
     * ファイルをS3に保存する(スタンダード)
     *
     * @param string $data
     * @param string $bucket
     * @param string $filePath
     * @param string $contentType
     * @param string|null $acl
     * @return Result
     * @throws Exception
     */
    public function saveToS3StorageClassStandard(
        string $data,
        string $bucket,
        string $filePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): Result {
        return $this->saveToS3($data, null, $bucket, $filePath, $contentType, $acl, static::STORAGE_CLASS_STANDARD);
    }

    /**
     * ファイルをS3に保存する(スタンダードIA)
     *
     * @param string $encodedImage
     * @param string $bucket
     * @param string $filePath
     * @param string $contentType
     * @param string|null $acl
     * @return Result
     * @throws Exception
     */
    public function saveToS3StorageClassStandardIA(
        string $encodedImage,
        string $bucket,
        string $filePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): Result {
        return $this->saveToS3($encodedImage, null, $bucket, $filePath, $contentType, $acl, static::STORAGE_CLASS_STANDARD_IA);
    }

    /**
     * ファイルをS3に保存する(1ゾーン低頻度)
     * @param string $data
     * @param string $bucket
     * @param string $destinationFilePath
     * @param string $contentType
     * @param string|null $acl
     * @return Result
     * @throws Exception
     */
    public function saveToS3StorageClassOneZoneIA(
        string $data,
        string $bucket,
        string $destinationFilePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): Result {
        return $this->saveToS3($data, null, $bucket, $destinationFilePath, $contentType, $acl, static::STORAGE_CLASS_ONEZONE_IA);
    }

    /**
     * ファイルをS3に保存する(インテリジェントティアリング)
     * @param string $data
     * @param string $bucket
     * @param string $destinationFilePath
     * @param string $contentType
     * @param string|null $acl
     * @return Result
     * @throws Exception
     */
    public function saveToS3StorageClassIntelligentTiering(
        string $data,
        string $bucket,
        string $destinationFilePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): Result {
        return $this->saveToS3($data, null, $bucket, $destinationFilePath, $contentType, $acl, static::STORAGE_CLASS_INTELLIGENT_TIERING);
    }


    /**
     * 削除する
     *
     * @param string $bucket
     * @param string[] $keys
     * @return Result
     * @throws Exception
     */
    public function delete(string $bucket, array $keys): Result
    {
        return $this->client->deleteObjects([
            'Bucket' => $bucket,
            'Delete' => ['Objects' => array_map(fn($key) => ['Key' => $key], $keys)],
        ]);
    }


    /**
     * パスのprefixを指定してS3からファイルを消す
     * 例: hoge_images/* を消したい場合は、$prefix="hoge_images/" になる
     *
     * @param string $bucket
     * @param string $prefix
     * @throws Exception
     */
    public function deleteFromS3ByPrefix(string $bucket, string $prefix): Result
    {
        $targetKeys = $this->getFilePathListByPrefix($bucket, $prefix);
        return $this->delete($bucket, $targetKeys);
    }

    /**
     * @param string $bucket
     * @param string $prefix
     * @return string[]
     */
    protected function getFilePathListByPrefix(string $bucket, string $prefix): array
    {
        $iterator = $this->client->getIterator('ListObjects', array(
            'Bucket' => $bucket,
            'Prefix' => $prefix,
        ));
        $result = [];
        foreach ($iterator as $item) {
            $result[] = $item['Key'];
        }
        return $result;
    }
}
