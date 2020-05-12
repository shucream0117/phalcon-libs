<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

class Env
{
    // このキー名で環境変数を定義すると、環境判定をこのクラスで行うことが出来ます
    protected const VAR_NAME = 'ENV_NAME';

    // ローカル開発=development, ローカルテスト=testing, CI=ci, ステージング=staging, 本番=production,
    const PRODUCTION = 'production';
    const STAGING = 'staging';
    const DEVELOPMENT = 'development';
    const TESTING = 'testing';
    const CI = 'ci';

    /**
     * ローカルマシンでのテスト(PHPUnit)実行かどうか
     */
    public static function isLocalTest(): bool
    {
        return !self::isCI() && self::isPHPUnitMode();
    }

    /**
     * PHPUnitによる実行かどうか
     */
    public static function isPHPUnitMode(): bool
    {
        return defined('PHPUNIT_MODE') && PHPUNIT_MODE === true;
    }

    /**
     * CI環境による実行かどうか
     */
    public static function isCI(): bool
    {
        return self::getEnvName() === self::CI;
    }

    public static function isDevelopment(): bool
    {
        return self::getEnvName() === self::DEVELOPMENT;
    }

    /**
     * ステージング環境かどうか
     */
    public static function isStaging(): bool
    {
        return self::getEnvName() === self::STAGING;
    }

    /**
     * 本番環境かどうか
     */
    public static function isProduction(): bool
    {
        return self::getEnvName() === self::PRODUCTION;
    }

    public static function getEnvName(): string
    {
        if ($envName = getenv(static::VAR_NAME)) {
            return $envName;
        }
        throw new \RuntimeException('failed to get env name.');
    }
}
