<?php

namespace romanzipp\QueueMonitor\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use romanzipp\QueueMonitor\Controllers\Payloads\Metric;
use romanzipp\QueueMonitor\Controllers\Payloads\Metrics;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Services\QueueMonitor;

class ShowQueueMonitorController
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'status' => ['nullable', 'numeric', Rule::in(MonitorStatus::toArray())],
            'queue' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'payload_search' => ['nullable', 'string'],
        ]);

        $filters = [
            'status' => isset($data['status']) && $data['status'] !== '' && is_numeric($data['status']) ? (int)$data['status'] : null,
            'queue' => $data['queue'] ?? 'all',
            'name' => $data['name'] ?? null,
            'payload_search' => isset($data['payload_search']) && $data['payload_search'] !== '' && $data['payload_search'] ? (string)$data['payload_search'] : null,
        ];

        $jobsQuery = QueueMonitor::getModel()->newQuery()
            ->where('started_at', '>=', now()->subMonths(6));

        if (isset($filters['status']) && $filters['status'] !== null) {
            $jobsQuery->where('status', $filters['status']);
        }

        if (isset($filters['queue']) && $filters['queue'] !== "all") {
            $jobsQuery->where('queue', $filters['queue']);
        }

        if (isset($filters['name']) && $filters['name'] !== '') {
            $jobsQuery->where('name', 'like', "%{$filters['name']}%")
                ->orWhere('job_id', '=', $filters['name']);
        }

        if (isset($filters['payload_search']) && $filters['payload_search'] !== null) {
            $jobsQuery->where('data', 'like', "%{$filters['payload_search']}%");
        }

        $jobsQuery
            ->orderByDesc('started_at')
            ->orderByDesc('started_at_exact');

        $jobs = $jobsQuery->simplePaginate(config('queue-monitor.ui.per_page'))
            ->appends($request->all());

        $queues = Cache::remember('queue-monitor:queues:' . now()->format('Ymd'), 86400, function () {
            return QueueMonitor::getModel()
                ->select('queue')
                ->distinct()
                ->orderBy('queue')
                ->pluck('queue')
                ->toArray();
        });

        $metrics = null;
        if (config('queue-monitor.ui.show_metrics') &&
            "" == $filters['name'] &&
            null === $filters['status'] &&
            null === $filters['payload_search']) {
            $metrics = $this->collectMetrics($filters);
        }

        return view('queue-monitor::jobs', [
            'jobs' => $jobs,
            'filters' => $filters,
            'queues' => $queues,
            'metrics' => $metrics,
            'statuses' => MonitorStatus::toNamedArray(),
        ]);
    }

    /**
     * @param array $filters
     * @return Metrics
     */
    public function collectMetrics(array $filters): Metrics
    {
        $timeFrame = config('queue-monitor.ui.metrics_time_frame') ?? 2;

        $metrics = new Metrics();

        $expressionTotalTime = DB::raw('SUM(TIMESTAMPDIFF(SECOND, `started_at`, `finished_at`)) as `total_time_elapsed`');
        $expressionAverageTime = DB::raw('AVG(TIMESTAMPDIFF(SECOND, `started_at`, `finished_at`)) as `average_time_elapsed`');
        $count = DB::raw('COUNT(*) as count');

        $aggregationColumns = [
            $count,
            $expressionTotalTime,
            $expressionAverageTime,
        ];

        $aggregatedInfo = QueueMonitor::getModel()
            ->newQuery()
            ->select($aggregationColumns)
            ->where('status', '!=', MonitorStatus::RUNNING)
            ->where('started_at', '>=', Carbon::now()->subDays($timeFrame));

        if ('all' !== $filters['queue']) {
            $aggregatedInfo->where('queue', $filters['queue']);
        }

        $aggregatedInfo = $aggregatedInfo->first();

        $comparisonQuery = QueueMonitor::getModel()
            ->newQuery()
            ->select($aggregationColumns)
            ->where('status', '!=', MonitorStatus::RUNNING)
            ->where('started_at', '>=', Carbon::now()->subDays($timeFrame * 2))
            ->where('started_at', '<=', Carbon::now()->subDays($timeFrame));

        if ('all' !== $filters['queue']) {
            $comparisonQuery->where('queue', $filters['queue']);
        }

        $aggregatedComparisonInfo = $comparisonQuery->first();

        $failedJobsQuery = QueueMonitor::getModel()
            ->newQuery()
            ->select($count)
            ->where('retried', MonitorStatus::NOT_RETRY)
            ->where('status', MonitorStatus::FAILED)
            ->where('started_at', '>=', Carbon::now()->subDays());
        if ('all' !== $filters['queue']) {
            $failedJobsQuery->where('queue', $filters['queue']);
        };
        $failedJobs = $failedJobsQuery->first();

        if (null === $aggregatedInfo || null === $aggregatedComparisonInfo) {
            return $metrics;
        }

        return $metrics
            ->push(
                new Metric('Количество выполненных заданий', $aggregatedInfo->count ?? 0, $aggregatedComparisonInfo->count, '%d')
            )
            ->push(
                new Metric('Количество ошибок за сутки', $failedJobs->count ?? 0)
            )
            ->push(
                new Metric('Общее время выполнения', $aggregatedInfo->total_time_elapsed ?? 0, $aggregatedComparisonInfo->total_time_elapsed, '%ds')
            )
            ->push(
                new Metric('Среднее время выполнения', $aggregatedInfo->average_time_elapsed ?? 0, $aggregatedComparisonInfo->average_time_elapsed, '%0.2fs')
            );
    }
}
