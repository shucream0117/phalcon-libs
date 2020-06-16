<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Repositories;

use PDOException;
use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\ModelInterface;
use Shucream0117\PhalconLib\Exceptions\AbstractException;
use Shucream0117\PhalconLib\Exceptions\AbstractRuntimeException;
use Throwable;

abstract class AbstractRepository
{
    protected function generateDatabaseErrorMessageFromModel(ModelInterface $model, string $delimiter = ','): string
    {
        $messageStrList = array_map(fn(MessageInterface $m) => $m->getMessage(), $model->getMessages());
        return implode($delimiter, $messageStrList);
    }

    /**
     * 共通処理(save/update/delete) で失敗時にスローする例外を作る
     *
     * @param string $message
     * @return Throwable|AbstractRuntimeException|AbstractException
     */
    abstract protected static function createDatabaseException(string $message = ''): Throwable;

    /**
     * 保存
     *
     * @param ModelInterface $model
     * @throws Throwable
     */
    protected function save(ModelInterface $model): void
    {
        try {
            if (!$model->save()) {
                throw static::createDatabaseException($this->generateDatabaseErrorMessageFromModel($model));
            }
        } catch (PDOException $e) {
            throw static::createDatabaseException($e->getMessage());
        }
    }

    /**
     * 更新
     *
     * @param ModelInterface $model
     * @param array $data
     * @param string[] $whiteList
     * @param bool $boolToInt
     * @return ModelInterface
     * @throws Throwable
     */
    public function update(ModelInterface $model, array $data, array $whiteList = [], bool $boolToInt = true): ModelInterface
    {
        if (!$data) {
            return $model;
        }
        if ($boolToInt) {
            foreach ($data as $k => $v) {
                if (is_bool($v)) {
                    $data[$k] = (int)$v;
                }
            }
        }
        $model = $model->assign($data, $whiteList ?: null);
        $this->save($model);
        return $model;
    }

    /**
     * 削除
     *
     * @param ModelInterface $model
     * @throws Throwable
     */
    public function delete(ModelInterface $model): void
    {
        try {
            if (!$model->delete()) {
                $message = $this->generateDatabaseErrorMessageFromModel($model) ?: 'failed to delete record';
                throw static::createDatabaseException($message);
            }
        } catch (PDOException $e) {
            throw static::createDatabaseException($e->getMessage());
        }
    }
}
