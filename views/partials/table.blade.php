<table class="w-full rounded-md whitespace-no-wrap rounded-md border dark:border-gray-600 border-separate border-spacing-0">

    <thead class="rounded-t-md">

        <tr>
            <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600">@lang('Статус')</th>
            <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600">@lang('Задание')</th>
            <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600">@lang('Детали')</th>
            <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600">@lang('Время выполнения')</th>
            <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600">@lang('Запуск')</th>
            <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600">@lang('Ошибки')</th>
            <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600">@lang('Тело сообщения')</th>

            @if(config('queue-monitor.ui.allow_deletion') || config('queue-monitor.ui.allow_retry'))
                <th class="px-4 py-3 font-medium text-left text-xs text-gray-600 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-600"></th>
            @endif
        </tr>

    </thead>

    <tbody class="bg-gray-50 dark:bg-gray-700">

        @forelse($jobs as $job)

            <tr class="font-sm leading-relaxed">

                <td class="p-4 text-gray-800 dark:text-gray-300 text-sm leading-5 border-b border-gray-200 dark:border-gray-600">
                    @include('queue-monitor::partials.job-status', ['status' => $job->status])
                </td>

                <td class="p-4 text-gray-800 dark:text-gray-300 text-sm leading-5 font-medium border-b border-gray-200 dark:border-gray-600">

                    {{ $job->getBaseName() }}

                    <span class="ml-1 text-xs text-gray-600 dark:text-gray-400">
                        #{{ $job->job_id }}
                    </span>

                </td>

                <td class="p-4 text-gray-800 dark:text-gray-300 text-sm leading-5 border-b border-gray-200 dark:border-gray-600">

                    <div class="text-xs">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">@lang('Очередь'):</span>
                        <span class="font-semibold">{{ $job->queue }}</span>
                    </div>

                    <div class="text-xs">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">@lang('Попытки'):</span>
                        <span class="font-semibold">{{ $job->attempt }}</span>
                    </div>

                    @if($job->retried)
                        <div class="text-xs py-2">
                            <span class="bg-gray-300 font-medium p-1 rounded text-red dark:text-red-500">@lang('Перезапустили')</span>
                        </div>
                    @endif
                </td>

                <td class="p-4 text-gray-800 dark:text-gray-300 text-sm leading-5 border-b border-gray-200 dark:border-gray-600">
                    {{ $job->getElapsedInterval()->format('%H:%I:%S') }}
                </td>

                <td class="p-4 text-gray-800 dark:text-gray-300 text-sm leading-5 border-b border-gray-200 dark:border-gray-600">
                    {{ $job->started_at?->diffForHumans() }}<br><br>({{ $job->started_at}})
                </td>

                <td class="p-4 text-gray-800 dark:text-gray-300 text-sm leading-5 border-b border-gray-200 dark:border-gray-600">

                    @if($job->status != \romanzipp\QueueMonitor\Enums\MonitorStatus::SUCCEEDED && $job->exception_message !== null)

                        <textarea rows="4" class="w-64 text-xs p-1 border rounded text-red-600 dark:text-red-600" readonly>{{ $job->exception_message }}</textarea>

                    @else
                        -
                    @endif

                </td>
                <td class="p-4 text-gray-800 dark:text-gray-300 text-sm leading-5 border-b border-gray-200 dark:border-gray-600">
                    @if($job->data)
                        <textarea rows="4" class="w-64 text-xs p-1 border rounded @if(!$job->hasSucceeded())text-red-600 dark:text-red-600 @endif  text-green-800 dark:text-green-800" readonly>{{$job->data}}</textarea>
                    @else
                        -
                    @endif
                </td>

                @if(config('queue-monitor.ui.allow_deletion') || config('queue-monitor.ui.allow_retry'))

                    <td class="p-4 eading-5 border-b border-gray-200 dark:border-gray-600">
                        @if(config('queue-monitor.ui.allow_retry') && $job->canBeRetried())
                            <form action="{{ route('queue-monitor::retry', [$job]) }}" method="post">
                                @csrf
                                @method('patch')
                                <button class="px-3 py-2 bg-gray-200 dark:hover:bg-gray-200  text-xs font-medium rounded transition-colors duration-150" style="color: white; background-color: orange">
                                    @lang('Повторить')
                                </button>
                            </form>
                        @endif
                        @if(config('queue-monitor.ui.allow_deletion') && $job->isFinished())
                            <form action="{{ route('queue-monitor::destroy', [$job]) }}" method="post">
                                @csrf
                                @method('delete')
                                <button class="px-3 py-2 bg-transparent hover:bg-red-100 dark:hover:bg-red-800 text-red-800 dark:text-red-500 dark:hover:text-red-200 text-xs font-medium rounded transition-colors duration-150">
                                    @lang('Удалить')
                                </button>
                            </form>
                        @endif
                    </td>

                @endif

            </tr>

        @empty

            <tr>
                <td colspan="100" class="">
                    <div class="my-6">
                        <div class="text-center">
                            <div class="text-gray-500 text-lg">
                                @lang('Нет задач')
                            </div>
                        </div>
                    </div>
                </td>
            </tr>

        @endforelse

    </tbody>

    <tfoot class="bg-white dark:bg-transparent">

        <tr>
            <td colspan="100" class="px-2 py-4">
                <div class="flex justify-between">
                    <div class="pl-2 text-sm text-gray-600 dark:text-gray-400">
                        @lang('Показано')
                        @if($jobs->count() > 0)
                            <span class="font-medium">{{ $jobs->firstItem() }}</span> @lang('из')
                            <span class="font-medium">{{ $jobs->lastItem() }}</span>
                        @endif
                    </div>

                    <div>
                        @if(!$jobs->onFirstPage())
                        <a class="py-2 px-4 mx-1 text-xs font-medium @if(!$jobs->onFirstPage()) hover:bg-gray-300 cursor-pointer @else text-gray-600 cursor-not-allowed @endif rounded"
                           @if(!$jobs->onFirstPage()) href="{{ $jobs->previousPageUrl() }}" @endif>
                            @lang('Предыдущая')
                        </a>@endif
                        <a class="py-2 px-4 mx-1 text-xs font-medium @if($jobs->hasMorePages())  hover:bg-gray-300 cursor-pointer @else text-gray-600  cursor-not-allowed @endif rounded"
                           @if($jobs->hasMorePages()) href="{{ $jobs->url($jobs->currentPage() + 1) }}" @endif>
                            @lang('Следующая')
                        </a>
                    </div>
                </div>
            </td>
        </tr>

    </tfoot>

</table>
