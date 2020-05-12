<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Constants;

/**
 * @see https://docs.phalcon.io/4.0/en/events
 */
final class EventType
{
    const MICRO = 'micro';
    const MICRO_BEFORE_HANDLE_ROUTE = self::MICRO . ':beforeHandleRoute';
    const MICRO_BEFORE_EXECUTE_ROUTE = self::MICRO . ':beforeExecuteRoute';
    const MICRO_AFTER_EXECUTE_ROUTE = self::MICRO . ':afterExecuteRoute';
    const MICRO_AFTER_HANDLE_ROUTE = self::MICRO . ':afterHandleRoute';

    const DB = 'db';
}
