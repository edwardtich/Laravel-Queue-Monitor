@switch($status)

    @case(\romanzipp\QueueMonitor\Enums\MonitorStatus::QUEUED)
        <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-50">
            Ожидание
        </div>
        @break

    @case(\romanzipp\QueueMonitor\Enums\MonitorStatus::RUNNING)
        <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-blue-200 dark:bg-blue-600 text-blue-800 dark:text-blue-50">
            Выполняется
        </div>
        @break

    @case(\romanzipp\QueueMonitor\Enums\MonitorStatus::SUCCEEDED)
        <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-green-200 dark:bg-green-600 text-green-800 dark:text-green-50">
            Сделано
        </div>
        @break

    @case(\romanzipp\QueueMonitor\Enums\MonitorStatus::FAILED)
        <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-red-200 dark:bg-red-600 text-red-800 dark:text-red-50">
            Ошибка
        </div>
        @break

    @case(\romanzipp\QueueMonitor\Enums\MonitorStatus::STALE)
        <div class="inline-flex flex-1 px-2 text-xs font-medium leading-5 rounded-full bg-gray-700 dark:bg-black text-gray-200">
            Затхлый
        </div>
        @break

@endswitch
