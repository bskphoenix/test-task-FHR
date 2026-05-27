<?php

declare(strict_types=1);

namespace App\Services\UserExport;

final class UserCsvExportProgress
{
    public const string STATUS_IDLE = 'idle';

    public const string STATUS_RUNNING = 'running';

    public const string STATUS_COMPLETED = 'completed';

    public const string STATUS_FAILED = 'failed';

    public function __construct(
        public readonly string $status,
        public readonly string $stageLabel,
        public readonly float $progressPercent,
        public readonly int $processedCount,
        public readonly int $totalCount,
        public readonly float $elapsedSeconds,
        public readonly int $memoryCurrentBytes,
        public readonly int $memoryPeakBytes,
        public readonly ?string $downloadUrl = null,
        public readonly ?string $errorMessage = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'stage_label' => $this->stageLabel,
            'progress_percent' => $this->progressPercent,
            'processed_count' => $this->processedCount,
            'total_count' => $this->totalCount,
            'elapsed_seconds' => $this->elapsedSeconds,
            'memory_current_bytes' => $this->memoryCurrentBytes,
            'memory_peak_bytes' => $this->memoryPeakBytes,
            'download_url' => $this->downloadUrl,
            'error_message' => $this->errorMessage,
        ];
    }
}
