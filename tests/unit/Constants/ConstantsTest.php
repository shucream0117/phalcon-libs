<?php

namespace Tests\Unit\Constants;

use ReflectionClass;
use ReflectionException;
use Tests\Unit\TestBase;

class ConstantsTest extends TestBase
{
    /**
     * 定数とペアになるテキストが存在するか
     * @throws ReflectionException
     */
    public function testEachConstantsHaveText()
    {
        $classNames = self::getClassNames();
        foreach ($classNames as $fullClassName) {
            $reflection = new ReflectionClass($fullClassName);
            $constants = $reflection->getConstants();
            $constants = array_values($constants);

            if ($text = $reflection->getStaticProperties()['text'] ?? null) {
                if ($keys = (array_keys($text))) {
                    sort($constants);
                    sort($keys);
                    $this->assertEquals($keys, $constants);
                }
            }
        }
    }

    /**
     * 定数たちが重複していないかどうかのテスト
     * @throws ReflectionException
     */
    public function testCheckDuplication()
    {
        $classNames = self::getClassNames();
        foreach ($classNames as $fullClassName) {
            $reflection = new ReflectionClass($fullClassName);
            $constants = $reflection->getConstants();
            $this->assertSame($constants, array_unique($constants));
        }
    }

    protected static function getClassNames(): array
    {
        $constantsDir = __DIR__ . '/../../../src/Constants';
        $namespace = '\\Shucream0117\\PhalconLib\\Constants';
        $files = scandir($constantsDir);
        $result = [];
        foreach ($files as $file) {
            if (preg_match('/(.*)\.php$/', $file, $matches)) {
                $filePath = "{$constantsDir}/{$file}";
                require_once $filePath;
                $result[] = "{$namespace}\\{$matches[1]}";
            }
        }
        return $result;
    }
}
