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
                    $this->assertEqualsCanonicalizing($keys, $constants);
                }
            }

            if ($textByLang = $reflection->getStaticProperties()['textByLang'] ?? null) {
                foreach ($textByLang as $lang => $text) {
                    if ($keys = (array_keys($text))) {
                        sort($constants);
                        sort($keys);
                        $this->assertEqualsCanonicalizing($keys, $constants);
                    }
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
        return self::getClassNamesRecursively(__DIR__ . '/../../../src/Constants');
    }

    protected static function getClassNamesRecursively(
        string $dir,
        string $namespace = '\\Shucream0117\\PhalconLib\\Constants'
    ): array {
        $files = array_filter(scandir($dir), fn($fileName) => preg_match('/^\./', $fileName) !== 1);
        $result = [];
        foreach ($files as $file) {
            $path = "{$dir}/{$file}";
            if (preg_match('/(.*)\.php$/', $file, $matches)) {
                $result[] = "{$namespace}\\{$matches[1]}";
                continue;
            }

            if (is_dir($path)) {
                $result = array_merge($result, self::getClassNamesRecursively(
                    $path,
                    "{$namespace}\\{$file}"
                ));
            }
        }
        return $result;
    }
}
