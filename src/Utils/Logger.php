<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

use Phalcon\Helper\Arr;
use Phalcon\Logger\Adapter\Stream;

class Logger
{
    private \Phalcon\Logger $logger;

    public const LEVEL_CRITICAL = 'critical';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_INFO = 'info';
    public const LEVEL_DEBUG = 'debug';
    /** @var array 利用可能ログレベル一覧。 phalcon logger のログレベルとのマッピングも兼ねる */
    private const AVAILABLE_LEVELS = [
        self::LEVEL_CRITICAL => \Phalcon\Logger::CRITICAL,
        self::LEVEL_WARNING => \Phalcon\Logger::WARNING,
        self::LEVEL_INFO => \Phalcon\Logger::INFO,
        self::LEVEL_DEBUG => \Phalcon\Logger::DEBUG,
    ];

    /**
     * @param string $name ロガー名
     * @param string $logLevel ログレベル。このレベル未満のログは出力しない。
     * @param array $levelFileMap ログレベル(key)と出力先ファイル(value)のマッピングを表す連想配列。例：
     * <code>
     * [
     *     'critical' => '/path/to/critical.log',
     *     'warning' => '/path/to/warning.log',
     *     'info' => '/path/to/info.log',
     *     'debug' => '/path/to/debug.log',
     * ]
     * </code>
     */
    public function __construct(string $name, string $logLevel, array $levelFileMap)
    {
        $this->logger = new \Phalcon\Logger($name);
        $this->logger->setLogLevel(self::AVAILABLE_LEVELS[$logLevel]);
        $customJsonFormatter = new CustomJsonFormatter();
        foreach ($levelFileMap as $level => $file) {
            if (!isset($adapters[$file])) { // 同一ファイルに対して複数の adapter インスタンスを生成させない制御
                $adapters[$file] = (new Stream($file))->setFormatter($customJsonFormatter);
            }
            $this->logger->addAdapter($level, $adapters[$file]);
        }
    }

    public function critical(string $message, array $context = []): void
    {
        $this->getLoggerFor(self::LEVEL_CRITICAL)->critical($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->getLoggerFor(self::LEVEL_WARNING)->warning($message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->getLoggerFor(self::LEVEL_INFO)->info($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->getLoggerFor(self::LEVEL_DEBUG)->debug($message, $context);
    }

    /**
     * excludeAdapters() を使って、ログレベルの数だけ用意した adapter の中から、対象ログレベルの adapter のみを残すことで、レベルごとの出力ファイル切替を実現している
     * @param string $targetLevel
     * @return \Phalcon\Logger
     */
    private function getLoggerFor(string $targetLevel): \Phalcon\Logger
    {
        $excludedLevels = Arr::filter(array_keys(self::AVAILABLE_LEVELS), fn($level) => $level !== $targetLevel);
        return $this->logger->excludeAdapters($excludedLevels);
    }
}
