<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Utils;

/*
 * Phinxのマイグレーションで使う便利クラス
 */

class PhinxUtil
{
    const COLLATION_UTF8_BIN = 'utf8_bin';
    const COLLATION_UTF8_GENERAL_CI = 'utf8_general_ci';
    const COLLATION_UTF8MB4_GENERAL_CI = 'utf8mb4_general_ci';

    const ULID_LENGTH = 26;

    /**
     * id の共通設定氏
     * @param bool $autoIncrement
     * @param bool $null
     * @return array
     */
    public static function idColumnOption(bool $autoIncrement = true, bool $null = false): array
    {
        return [
            'identity' => $autoIncrement,
            'signed' => false,
            'null' => $null,
        ];
    }

    /**
     * ULID(https://github.com/ulid/spec) を主キーにする場合のオプション
     * @param bool $null
     * @return array
     */
    public static function ulidPkColumnOption(bool $null = false): array
    {
        return [
            'length' => static::ULID_LENGTH,
            'collation' => static::COLLATION_UTF8_BIN,
            'null' => $null,
        ];
    }

    /**
     * created_at の共通設定氏
     * @return array
     */
    public static function getCreatedAtColumnOption(): array
    {
        return [
            'null' => false,
            'default' => "CURRENT_TIMESTAMP",
        ];
    }

    /**
     * updated_at の共通設定氏
     * @return array
     */
    public static function getUpdatedAtColumnOption(): array
    {
        return [
            'null' => false,
            'default' => "CURRENT_TIMESTAMP",
            'update' => "CURRENT_TIMESTAMP",
        ];
    }

    /**
     * @param bool $check
     * @return string
     */
    public static function getForeignKeyCheckQuery(bool $check): string
    {
        $check = (int)$check;
        return "SET foreign_key_checks = {$check};";
    }
}
