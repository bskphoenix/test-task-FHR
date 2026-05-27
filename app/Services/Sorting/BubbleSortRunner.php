<?php

declare(strict_types=1);

namespace App\Services\Sorting;

final class BubbleSortRunner
{
    public const int DEFAULT_ELEMENT_COUNT = 200_000;

    public function __construct(
        private readonly NumericArrayGenerator $arrayGenerator,
        private readonly BubbleSortService $bubbleSortService,
    ) {}

    /**
     * @param list<int> $data
     * @param list<int> $originalData
     * @param list<array{time: string, message: string}> $steps
     */
    private function buildCancelledResult(
        array $data,
        array $originalData,
        float $startedAt,
        int $count,
        int $completedPasses,
        ?callable $onProgress,
        array &$steps,
        int $progressReportInterval,
    ): array {
        $this->reportProgress(
            onProgress: $onProgress,
            startedAt: $startedAt,
            count: $count,
            stage: BubbleSortProgress::STAGE_CANCELLED,
            stageLabel: 'Сортировка остановлена пользователем',
            progressPercent: min(
                99,
                8 + ($completedPasses / max(1, $count - 1)) * 86,
            ),
            steps: $steps,
            stepMessage: sprintf(
                'Остановка после %s проходов. Показан промежуточный результат.',
                number_format($completedPasses, 0, ',', ' '),
            ),
            status: BubbleSortProgress::STATUS_CANCELLED,
            pass: $completedPasses,
            estimatedPasses: max(1, $count - 1),
            cancelled: true,
            progressReportInterval: $progressReportInterval,
        );

        return $this->buildResult(
            data: $data,
            originalData: $originalData,
            startedAt: $startedAt,
            count: $count,
            completedPasses: $completedPasses,
            cancelled: true,
        );
    }

    /**
     * @param list<int> $data
     * @param list<int> $originalData
     * @return array{
     *     count: int,
     *     duration_seconds: float,
     *     memory_current_bytes: int,
     *     memory_peak_bytes: int,
     *     elements: list<int>,
     *     original_elements: list<int>,
     *     is_sorted: bool,
     *     cancelled: bool,
     *     completed_passes: int,
     * }
     */
    private function buildResult(
        array $data,
        array $originalData,
        float $startedAt,
        int $count,
        int $completedPasses,
        bool $cancelled,
    ): array {
        return [
            'count' => $count,
            'duration_seconds' => microtime(true) - $startedAt,
            'memory_current_bytes' => memory_get_usage(true),
            'memory_peak_bytes' => memory_get_peak_usage(true),
            'elements' => $data,
            'original_elements' => $originalData,
            'is_sorted' => $data !== [] && $this->isSorted($data),
            'cancelled' => $cancelled,
            'completed_passes' => $completedPasses,
        ];
    }

    /**
     * Проверяет, что массив отсортирован по возрастанию.
     *
     * @param list<int> $data
     */
    private function isSorted(array $data): bool
    {
        $count = count($data);

        for ($index = 1; $index < $count; $index++) {
            if ($data[$index - 1] > $data[$index]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array{time: string, message: string}> $steps
     */
    private function reportProgress(
        ?callable $onProgress,
        float $startedAt,
        int $count,
        string $stage,
        string $stageLabel,
        float $progressPercent,
        array &$steps,
        string $stepMessage,
        string $status = BubbleSortProgress::STATUS_RUNNING,
        int $pass = 0,
        int $estimatedPasses = 0,
        bool $cancelled = false,
        int $progressReportInterval = BubbleSortService::PROGRESS_REPORT_INTERVAL,
    ): void {
        if ($onProgress === null) {
            return;
        }

        $steps[] = [
            'time' => now()->format('H:i:s'),
            'message' => $stepMessage,
        ];

        if (count($steps) > 12) {
            $steps = array_slice($steps, -12);
        }

        $onProgress(new BubbleSortProgress(
            status: $status,
            stage: $stage,
            stageLabel: $stageLabel,
            progressPercent: round($progressPercent, 1),
            elapsedSeconds: microtime(true) - $startedAt,
            memoryCurrentBytes: memory_get_usage(true),
            memoryPeakBytes: memory_get_peak_usage(true),
            elementCount: $count,
            pass: $pass,
            estimatedPasses: $estimatedPasses,
            progressReportInterval: $progressReportInterval,
            steps: $steps,
            cancelled: $cancelled,
        ));
    }

    private function shouldCancel(?callable $shouldCancel): bool
    {
        return $shouldCancel !== null && $shouldCancel();
    }

    private function formatMegabytes(int $bytes): string
    {
        return number_format($bytes / 1024 / 1024, 2, ',', ' ');
    }

    /**
     * Подготавливает пошаговую сортировку: генерирует массив и сохраняет сессию.
     *
     * @param callable(BubbleSortProgress): void|null $onProgress
     */
    public function startSteppedRun(
        string $runId,
        BubbleSortSessionStore $sessionStore,
        int $count,
        string $ownerKey,
        ?callable $onProgress = null,
        int $progressReportInterval = BubbleSortService::PROGRESS_REPORT_INTERVAL,
    ): void {
        $startedAt = microtime(true);
        $progressReportInterval = max(1, $progressReportInterval);

        /** @var list<array{time: string, message: string}> $steps */
        $steps = [];

        $this->reportProgress(
            onProgress: $onProgress,
            startedAt: $startedAt,
            count: $count,
            stage: BubbleSortProgress::STAGE_GENERATING,
            stageLabel: 'Генерация массива...',
            progressPercent: 2,
            steps: $steps,
            stepMessage: sprintf('Начата генерация массива из %s элементов', number_format($count, 0, ',', ' ')),
            progressReportInterval: $progressReportInterval,
        );

        $data = $this->arrayGenerator->generate($count);
        $originalData = $data;

        $this->reportProgress(
            onProgress: $onProgress,
            startedAt: $startedAt,
            count: $count,
            stage: BubbleSortProgress::STAGE_GENERATING,
            stageLabel: 'Массив сгенерирован, запуск сортировки...',
            progressPercent: 8,
            steps: $steps,
            stepMessage: sprintf(
                'Массив сгенерирован. Текущая память: %s МБ',
                $this->formatMegabytes(memory_get_usage(true)),
            ),
            progressReportInterval: $progressReportInterval,
        );

        $sessionStore->create(
            runId: $runId,
            data: $data,
            startedAt: $startedAt,
            progressReportInterval: $progressReportInterval,
            steps: $steps,
            ownerKey: $ownerKey,
        );
    }

    /**
     * Выполняет один шаг пошаговой сортировки.
     *
     * @param callable(BubbleSortProgress): void|null $onProgress
     * @param callable(): bool|null $shouldCancel
     * @return array{continue: bool, result: array<string, mixed>|null}
     */
    public function processStep(
        string $runId,
        BubbleSortSessionStore $sessionStore,
        ?callable $onProgress = null,
        ?callable $shouldCancel = null,
    ): array {
        $session = $sessionStore->get($runId);

        if ($session === null) {
            return ['continue' => false, 'result' => null];
        }

        $data = $session['data'];
        $count = $session['count'];
        $pass = $session['pass'];
        $upperBound = $session['upper_bound'];
        $startedAt = $session['started_at'];
        $progressReportInterval = $session['progress_report_interval'];

        /** @var list<array{time: string, message: string}> $steps */
        $steps = $session['steps'];
        $originalData = $sessionStore->getOriginalData($runId) ?? [];

        if ($this->shouldCancel($shouldCancel)) {
            $result = $this->buildCancelledResult(
                data: $data,
                originalData: $originalData,
                startedAt: $startedAt,
                count: $count,
                completedPasses: $pass,
                onProgress: $onProgress,
                steps: $steps,
                progressReportInterval: $progressReportInterval,
            );

            $sessionStore->forget($runId);

            return ['continue' => false, 'result' => $result];
        }

        $batch = $this->bubbleSortService->sortBatch(
            $data,
            $pass,
            $upperBound,
            BubbleSortService::STEP_BATCH_PASSES,
            $shouldCancel,
        );

        $estimatedPasses = max(1, $count - 1);
        $progressPercent = 8 + min(86, ($batch['pass'] / $estimatedPasses) * 86);

        $this->reportProgress(
            onProgress: $onProgress,
            startedAt: $startedAt,
            count: $count,
            stage: BubbleSortProgress::STAGE_SORTING,
            stageLabel: sprintf(
                'Сортировка: проход %s из ~%s',
                number_format($batch['pass'], 0, ',', ' '),
                number_format($estimatedPasses, 0, ',', ' '),
            ),
            progressPercent: $progressPercent,
            steps: $steps,
            stepMessage: sprintf(
                'Проход %s / ~%s. Память: %s МБ, пик: %s МБ',
                number_format($batch['pass'], 0, ',', ' '),
                number_format($estimatedPasses, 0, ',', ' '),
                $this->formatMegabytes(memory_get_usage(true)),
                $this->formatMegabytes(memory_get_peak_usage(true)),
            ),
            pass: $batch['pass'],
            estimatedPasses: $estimatedPasses,
            progressReportInterval: $progressReportInterval,
        );

        if ($batch['cancelled']) {
            $result = $this->buildCancelledResult(
                data: $data,
                originalData: $originalData,
                startedAt: $startedAt,
                count: $count,
                completedPasses: $batch['pass'],
                onProgress: $onProgress,
                steps: $steps,
                progressReportInterval: $progressReportInterval,
            );

            $sessionStore->forget($runId);

            return ['continue' => false, 'result' => $result];
        }

        if ($batch['completed']) {
            $this->reportProgress(
                onProgress: $onProgress,
                startedAt: $startedAt,
                count: $count,
                stage: BubbleSortProgress::STAGE_VERIFYING,
                stageLabel: 'Проверка корректности сортировки...',
                progressPercent: 96,
                steps: $steps,
                stepMessage: 'Проверка, что массив отсортирован по возрастанию',
                progressReportInterval: $progressReportInterval,
            );

            $isSorted = $this->isSorted($data);

            $this->reportProgress(
                onProgress: $onProgress,
                startedAt: $startedAt,
                count: $count,
                stage: BubbleSortProgress::STAGE_COMPLETED,
                stageLabel: 'Сортировка завершена',
                progressPercent: 100,
                steps: $steps,
                stepMessage: $isSorted
                    ? 'Массив успешно отсортирован'
                    : 'Обнаружена ошибка при проверке сортировки',
                status: BubbleSortProgress::STATUS_COMPLETED,
                progressReportInterval: $progressReportInterval,
            );

            $result = $this->buildResult(
                data: $data,
                originalData: $originalData,
                startedAt: $startedAt,
                count: $count,
                completedPasses: $batch['pass'],
                cancelled: false,
            );

            $sessionStore->forget($runId);

            return ['continue' => false, 'result' => $result];
        }

        $sessionStore->update(
            runId: $runId,
            data: $data,
            pass: $batch['pass'],
            upperBound: $batch['upper_bound'],
            steps: $steps,
        );

        return ['continue' => true, 'result' => null];
    }
}
