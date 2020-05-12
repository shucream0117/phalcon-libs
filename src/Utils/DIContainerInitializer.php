<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use Closure;
use InvalidArgumentException;
use Phalcon\Di;

class DIContainerInitializer
{
    /**
     * @param Di $container
     * @param array $shared
     * @param array $notShared sharedじゃないクラス達
     * [
     *     Hoge::class, // 基本的にはクラス名だけを渡せば、そのクラス名をキーにしてインスタンスを自動登録する
     *     [Fuga::class, fn() => new Fuga(1, 2)] // インスタンスを返すクロージャを指定する場合は配列にする
     * ]
     * @return Di
     */
    public static function init(Di $container, array $shared, array $notShared): Di
    {
        $f = function ($data) {
            /** @var Closure|null $func */
            $func = null;
            if (is_array($data)) {
                $className = $data[0];
                $func = $data[1] ?? null;
            } elseif (is_string($data)) {
                $className = $data;
            } else {
                throw new InvalidArgumentException('invalid parameter type');
            }
            return [$className, $func];
        };
        foreach ($shared as $data) {
            $result = $f($data);
            $className = $result[0];
            $func = $result[1];
            $container->setShared($className, $func ?: fn() => new $className);
        }

        foreach ($notShared as $data) {
            $result = $f($data);
            $className = $result[0];
            $func = $result[1];
            $container->set($className, $func ?: fn() => new $className);
        }
        return $container;
    }
}
