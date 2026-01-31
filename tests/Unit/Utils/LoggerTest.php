<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Shucream0117\PhalconLib\Utils\Json;
use Shucream0117\PhalconLib\Utils\Logger;
use Tests\Unit\TestBase;

class LoggerTest extends TestBase
{
    private string $fileCritical = '/tmp/LoggerTest-1.log';
    private string $fileWarning = '/tmp/LoggerTest-2.log';
    private string $fileInfoAndDebug = '/tmp/LoggerTest-3.log';

    protected function setUp(): void
    {
        parent::setUp();
        $unlink = function ($file) {
            if (is_file($file)) {
                unlink($file);
            }
        };
        $unlink($this->fileCritical);
        $unlink($this->fileWarning);
        $unlink($this->fileInfoAndDebug);
    }

    public function testLogFile(): string
    {
        $logger = new Logger('logger-name-test', Logger::LEVEL_DEBUG, [
            Logger::LEVEL_CRITICAL => $this->fileCritical,
            Logger::LEVEL_WARNING => $this->fileWarning,
            Logger::LEVEL_INFO => $this->fileInfoAndDebug,
            Logger::LEVEL_DEBUG => $this->fileInfoAndDebug, // 同じファイルでもいけるというのを試しておく
        ]);
        $logger->critical('test critical');
        $logger->warning('test warning');
        $logger->info('test info');
        $logger->debug('test debug');

        $this->assertStringContainsString('test critical', $outputLine = file_get_contents($this->fileCritical));
        $this->assertStringContainsString('test warning', file_get_contents($this->fileWarning));
        $this->assertStringContainsString('test info', file_get_contents($this->fileInfoAndDebug));
        $this->assertStringContainsString('test debug', file_get_contents($this->fileInfoAndDebug));

        return $outputLine;
    }

    public function testJsonFormatter(): void
    {
        $logger = new Logger('logger-name-test', Logger::LEVEL_DEBUG, [
            Logger::LEVEL_CRITICAL => $this->fileCritical,
            Logger::LEVEL_WARNING => $this->fileWarning,
            Logger::LEVEL_INFO => $this->fileInfoAndDebug,
            Logger::LEVEL_DEBUG => $this->fileInfoAndDebug, // 同じファイルでもいけるというのを試しておく
        ]);
        $logger->critical('test critical');
        $logger->warning('test warning');
        $logger->info('test info');
        $logger->debug('test debug');


        // タイムスタンプの形式を確認
        // "2024-04-05T15:02:56+00:00" のような形式を想定するため、パターンマッチで確認
        $timestampPattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/';

        $criticalLog = Json::decode(file_get_contents($this->fileCritical));
        $this->assertSame('critical', $criticalLog['type']);
        $this->assertSame('test critical', $criticalLog['message']);
        $this->assertMatchesRegularExpression($timestampPattern, $criticalLog['timestamp']);
    }

    public function testLogLevel(): void
    {
        // ログレベル warning のときは warning 以上が出力される
        $logger = new Logger('logger-name-test', Logger::LEVEL_WARNING, [
            Logger::LEVEL_CRITICAL => $this->fileCritical,
            Logger::LEVEL_WARNING => $this->fileWarning,
            Logger::LEVEL_INFO => $this->fileInfoAndDebug,
            Logger::LEVEL_DEBUG => $this->fileInfoAndDebug,
        ]);
        $logger->critical('test critical');
        $logger->warning('test warning');
        $logger->info('test info');
        $logger->debug('test debug');

        $this->assertStringContainsString('test critical', file_get_contents($this->fileCritical));
        $this->assertStringContainsString('test warning', file_get_contents($this->fileWarning));
        $this->assertFileDoesNotExist($this->fileInfoAndDebug); // 何も出力されないのでファイルがない
    }

    public function testLogWithContext()
    {
        $logger = new Logger('logger-name-test', Logger::LEVEL_CRITICAL, [
            Logger::LEVEL_CRITICAL => $this->fileCritical,
        ]);

        // 一旦シンプルな形で動作確認
        $contest = ['key' => 'value'];
        $logger->critical('test info with context', $contest);
        $logStr = file_get_contents($this->fileCritical);
        $this->assertStringContainsString('"context":{"key":"value"}', $logStr);


        // contextの書き出しもテスト
        $context = [
            'int' => 1,
            'string' => 'string',
            'array' => ['a', 'b', 'c'],
            'nested_array' => [
                'key1' => [
                    'key2' => 'value',
                ],
            ],
            'object' => new \stdClass(),
            'null' => null,
            'bool' => true,
        ];
        $logger->critical('test info with context', $context);
        $logStr = file_get_contents($this->fileCritical);

        $expected = '"context":{"int":1,"string":"string","array":["a","b","c"],"nested_array":{"key1":{"key2":"value"}},"object":{},"null":null,"bool":true}';
        $this->assertStringContainsString($expected, $logStr);

        // 単一のキーしか存在しない場合で、そのキーに対応する値が配列の場合に Array to string conversion の Notice が出る問題がライブラリ側にあり
        // それを CustomJsonFormatter で解消しているので、そのテストも行う
        $context = [
            'nested_array' => [
                'key1' => [
                    'key2' => 'value',
                ],
            ],
        ];
        $logger->critical('test info with context', $context);
        $logStr = file_get_contents($this->fileCritical);

        $expected = '"context":{"nested_array":{"key1":{"key2":"value"}}}';
        $this->assertStringContainsString($expected, $logStr);
    }
}
