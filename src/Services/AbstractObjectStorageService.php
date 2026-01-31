<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Services;

use Aws\Result;
use Aws\S3\S3Client;
use Exception;

/**
 * S3/R2 などのS3互換オブジェクトストレージサービスの抽象クラス
 */
class AbstractObjectStorageService
{
    public const STORAGE_CLASS_STANDARD = 'STANDARD'; // 標準
    public const STORAGE_CLASS_STANDARD_IA = 'STANDARD_IA'; // 標準低頻度

    public const ACL_PRIVATE = 'private';

    public const METADATA_DIRECTIVE_COPY = 'COPY';
    public const METADATA_DIRECTIVE_REPLACE = 'REPLACE';

    private S3Client $client;

    public function __construct(S3Client $client)
    {
        $this->client = $client;
    }

    public function getS3Client(): S3Client
    {
        return $this->client;
    }

    /**
     * オブジェクトストレージに保存する
     *
     * @param string|null $data
     * @param string|null $sourceFilePath
     * @param string $bucket
     * @param string $filePath
     * @param string $contentType
     * @param string|null $acl
     * @param string|null $storageClass
     * @param array<string, mixed> $options
     * @return Result
     * @throws Exception
     */
    private function putObject(
        ?string $data,
        ?string $sourceFilePath,
        string $bucket,
        string $filePath,
        string $contentType,
        string $acl,
        ?string $storageClass,
        array $options = []
    ): Result {
        if (!$data && !$sourceFilePath) {
            throw new Exception('data or sourceFilePath is required');
        }
        if ($data && $sourceFilePath) {
            throw new Exception('data and sourceFilePath are exclusive');
        }
        $params = array_merge([
            'Bucket' => $bucket,
            'Key' => $filePath,
            'ACL' => $acl,
            'ContentType' => $contentType,
        ], $options);
        if ($data) {
            $params['Body'] = $data;
        }
        if ($sourceFilePath) {
            $params['SourceFile'] = $sourceFilePath;
        }
        if ($storageClass) {
            $params['StorageClass'] = $storageClass;
        }
        return $this->client->putObject($params);
    }

    /**
     * ファイルを保存する
     *
     * @param string $data
     * @param string $bucket
     * @param string $filePath
     * @param string $contentType
     * @param string|null $acl
     * @param string|null $storageClass
     * @return Result
     * @throws Exception
     */
    public function save(
        string $data,
        string $bucket,
        string $filePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE,
        ?string $storageClass = self::STORAGE_CLASS_STANDARD,
        array $options = []
    ): Result {
        return $this->putObject($data, null, $bucket, $filePath, $contentType, $acl, $storageClass, $options);
    }

    /**
     * ローカルのファイルパスを指定して保存する
     *
     * @param string $sourceFilePath
     * @param string $bucket
     * @param string $destinationFilePath
     * @param string $contentType
     * @param string|null $acl
     * @param string|null $storageClass
     * @return Result
     * @throws Exception
     */
    public function saveFromFilePath(
        string $sourceFilePath,
        string $bucket,
        string $destinationFilePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE,
        ?string $storageClass = self::STORAGE_CLASS_STANDARD,
        array $options = []
    ): Result {
        return $this->putObject(null, $sourceFilePath, $bucket, $destinationFilePath, $contentType, $acl, $storageClass, $options);
    }

    /**
     * ファイルをコピーする
     *
     * @param string $sourceBucket
     * @param string $sourceFilePath
     * @param string $destinationBucket
     * @param string $destinationFilePath
     * @param string|null $storageClass
     * @param string $metadataDirective
     * @param string|null $acl
     * @return Result
     */
    public function copy(
        string $sourceBucket,
        string $sourceFilePath,
        string $destinationBucket,
        string $destinationFilePath,
        string $metadataDirective = self::METADATA_DIRECTIVE_COPY,
        ?string $acl = self::ACL_PRIVATE,
        ?string $storageClass = null
    ): Result {
        $params = [
            'CopySource' => "{$sourceBucket}/{$sourceFilePath}",
            'Bucket' => $destinationBucket,
            'Key' => $destinationFilePath,
            'MetadataDirective' => $metadataDirective,
            'ACL' => $acl,
        ];
        if ($storageClass) {
            $params['StorageClass'] = $storageClass;
        }
        return $this->client->copyObject($params);
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
     * パスのprefixを指定してファイルを消す
     * 例: hoge_images/* を消したい場合は、$prefix="hoge_images/" になる
     *
     * @param string $bucket
     * @param string $prefix
     * @return Result
     * @throws Exception
     */
    public function deletePrefix(string $bucket, string $prefix): Result
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
