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

    /**
     * @depends testLogFile
     * @param string $outputLine
     */
    public function testJsonFormatter(string $outputLine): void
    {
        $decodedLog = Json::decode($outputLine);
        $this->assertArrayHasKey('type', $decodedLog);
        $this->assertArrayHasKey('message', $decodedLog);
        $this->assertArrayHasKey('timestamp', $decodedLog);
        $this->assertArrayHasKey('context', $decodedLog);
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
}
