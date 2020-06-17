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
     * @param string $data
     * @param string $bucket
     * @param string $filePath
     * @param string $contentType
     * @param string|null $acl
     * @param string $storageClass
     * @return Result
     * @throws Exception
     */
    private function saveToS3(
        string $data,
        string $bucket,
        string $filePath,
        string $contentType,
        string $acl,
        string $storageClass
    ): Result {
        return $this->client->putObject([
            'Bucket' => $bucket,
            'Key' => $filePath,
            'Body' => $data,
            'ACL' => $acl,
            'ContentType' => $contentType,
            'StorageClass' => $storageClass,
        ]);
    }

    /**
     * ファイルをS3(スタンダード)に保存する
     *
     * @param string $data
     * @param string $bucket
     * @param string $filePath
     * @param string $contentType
     * @param string|null $acl
     * @throws Exception
     */
    public function saveToS3StorageClassStandard(
        string $data,
        string $bucket,
        string $filePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): void {
        $this->saveToS3($data, $bucket, $filePath, $contentType, $acl, static::STORAGE_CLASS_STANDARD);
    }

    /**
     * ファイルをS3(スタンダードIA)に保存する
     *
     * @param string $encodedImage
     * @param string $bucket
     * @param string $filePath
     * @param string $contentType
     * @param string|null $acl
     * @throws Exception
     */
    public function saveToS3StorageClassStandardIA(
        string $encodedImage,
        string $bucket,
        string $filePath,
        string $contentType,
        ?string $acl = self::ACL_PRIVATE
    ): void {
        $this->saveToS3($encodedImage, $bucket, $filePath, $contentType, $acl, static::STORAGE_CLASS_STANDARD_IA);
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
    public function deleteFromS3ByPrefix(string $bucket, string $prefix): void
    {
        $targetKeys = $this->getFilePathListByPrefix($bucket, $prefix);
        $this->delete($bucket, $targetKeys);
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
