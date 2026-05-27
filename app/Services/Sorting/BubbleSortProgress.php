<?php

declare(strict_types=1);

namespace App\Services\Sorting;

final class BubbleSortProgress
{
    public const string STAGE_GENERATING = 'generating';

    public const string STAGE_SORTING = 'sorting';

    public const string STAGE_VERIFYING = 'verifying';

    public const string STAGE_COMPLETED = 'completed';

    public const string STAGE_CANCELLED = 'cancelled';

    public const string STATUS_RUNNING = 'running';

    public const string STATUS_COMPLETED = 'completed';

    public const string STATUS_CANCELLED = 'cancelled';

    /**
     * @param list<array{time: string, message: string}> $steps
     */
    public function __construct(
        public readonly string $status,
        public readonly string $stage,
        public readonly string $stageLabel,
        public readonly float $progressPercent,
        public readonly float $elapsedSeconds,
        public readonly int $memoryCurrentBytes,
        public readonly int $memoryPeakBytes,
        public readonly int $elementCount,
        public readonly int $pass = 0,
        public readonly int $estimatedPasses = 0,
        public readonly int $progressReportInterval = BubbleSortService::PROGRESS_REPORT_INTERVAL,
        public readonly array $steps = [],
        public readonly bool $cancelled = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'stage' => $this->stage,
            'stage_label' => $this->stageLabel,
            'progress_percent' => $this->progressPercent,
            'elapsed_seconds' => $this->elapsedSeconds,
            'memory_current_bytes' => $this->memoryCurrentBytes,
            'memory_peak_bytes' => $this->memoryPeakBytes,
            'element_count' => $this->elementCount,
            'pass' => $this->pass,
            'estimated_passes' => $this->estimatedPasses,
            'progress_report_interval' => $this->progressReportInterval,
            'steps' => $this->steps,
            'cancelled' => $this->cancelled,
        ];
    }
}
