<?php

declare(strict_types=1);

namespace App\Services\Sorting;

use RuntimeException;

final class BubbleSortService
{
    public function __construct(
        private readonly NativeBubbleSort $nativeBubbleSort,
    ) {}

    /** Доступна ли нативная пузырьковая сортировка */
    public function isAvailable(): bool
    {
        return $this->nativeBubbleSort->isAvailable();
    }

    public function unavailableReason(): ?string
    {
        return $this->nativeBubbleSort->unavailableReason();
    }

    /**
     * Полная сортировка по файлу до завершения.
     *
     * @param callable(int $pass, int $estimatedPasses, float $elapsedSeconds): void|null $onProgress
     * @return array{completed: bool, pass: int, duration_seconds: float, engine: string}
     */
    public function sortFileInPlace(
        string $sortedFilePath,
        int $elementCount,
        ?callable $onProgress = null,
        int $progressInterval = 500,
    ): array {
        $this->ensureAvailable();

        $startedAt = microtime(true);
        $pass = 0;
        $upperBound = max(0, $elementCount - 1);
        $estimatedPasses = max(1, $upperBound);
        $progressInterval = max(1, $progressInterval);

        while ($upperBound > 0) {
            $batch = $this->nativeBubbleSort->sortFileBatch(
                $sortedFilePath,
                $pass,
                $upperBound,
                $progressInterval,
            );

            if ($onProgress !== null) {
                $onProgress($pass, $estimatedPasses, microtime(true) - $startedAt);
            }

            if ($batch['completed']) {
                return [
                    'completed' => true,
                    'pass' => $pass,
                    'duration_seconds' => microtime(true) - $startedAt,
                    'engine' => 'native',
                ];
            }
        }

        return [
            'completed' => true,
            'pass' => $pass,
            'duration_seconds' => microtime(true) - $startedAt,
            'engine' => 'native',
        ];
    }

    private function ensureAvailable(): void
    {
        if ($this->isAvailable()) {
            return;
        }

        throw new RuntimeException(
            $this->unavailableReason() ?? 'Нативная сортировка недоступна.',
        );
    }
}
