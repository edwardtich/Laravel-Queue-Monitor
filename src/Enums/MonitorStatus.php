<?php

namespace romanzipp\QueueMonitor\Enums;

class MonitorStatus
{
    public const RUNNING = 0;
    public const SUCCEEDED = 1;
    public const FAILED = 2;
    public const STALE = 3;
    public const QUEUED = 4;
    public const NOT_RETRY = 0;
    public const RETRY = 1;

    /**
     * @return int[]
     */
    public static function toArray(): array
    {
        return [
            self::RUNNING,
            self::SUCCEEDED,
            self::FAILED,
            self::STALE,
            self::QUEUED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function toNamedArray(): array
    {
        return [
            self::RUNNING => 'Выполняется',
            self::SUCCEEDED => 'Сделано',
            self::FAILED => 'Ошибка',
            self::STALE => 'Затхлый',
            self::QUEUED => 'Ожидание',
        ];
    }
}
