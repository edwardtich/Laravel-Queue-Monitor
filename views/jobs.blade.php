<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if(config('queue-monitor.ui.refresh_interval'))
        <meta http-equiv="refresh" content="{{ config('queue-monitor.ui.refresh_interval') }}">
    @endif
    <title>@lang('Queue Monitor')</title>
    <link href="{{ asset('vendor/queue-monitor/app.css') }}" rel="stylesheet">
</head>

<body class="font-sans pb-64 bg-white dark:bg-gray-800 dark:text-white">
<div class="p-4">
    <a href="/"
       class="rounded shrink inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-brand-800 dark:hover:text-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:focus:ring-brand-700 mt-0">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
             stroke="currentColor" aria-hidden="true" class="h-3 w-3 mr-1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
        </svg> Back to Booking system</a>
</div>
    <nav class="flex items-center py-4 border-b border-gray-100 dark:border-gray-600">
        <h1 class="px-4 w-full font-semibold text-lg">
            @lang('Мониторинг Очереди')
        </h1>
        @if($metrics)
        <div class="w-[24rem] px-4 font-semibold text-lg">
            @lang('Статистика')
        </div>
        @endif
    </nav>

    <main class="flex">

        <article class="w-full p-4">
            <h2 class="mb-4 text-gray-800 text-sm font-medium">
                @lang('Filter')
            </h2>

            @include('queue-monitor::partials.filter', [
                'filters' => $filters,
            ])

            <h2 class="mb-4 text-gray-800 text-sm font-medium">
                @lang('Jobs')
            </h2>

            @include('queue-monitor::partials.table', [
                'jobs' => $jobs,
            ])

            @if(config('queue-monitor.ui.allow_purge'))
                <div class="mt-12">
                    <form action="{{ route('queue-monitor::purge') }}" method="post">
                        @csrf
                        @method('delete')
                        <button class="py-2 px-4 bg-red-50 dark:bg-red-200 hover:dark:bg-red-300 hover:bg-red-100 text-red-800 text-xs font-medium rounded-md transition-colors duration-150">
                            @lang('Удалить все записи')
                        </button>
                    </form>
                </div>
            @endif
        </article>

        @if($metrics)
        <aside class="flex flex-col gap-4 w-[24rem] p-4">
            @foreach($metrics->all() as $metric)
                @include('queue-monitor::partials.metrics-card', [
                    'metric' => $metric,
                ])
            @endforeach
        </aside>
        @endif

    </main>

</body>

</html>
