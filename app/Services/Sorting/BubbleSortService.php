<?php

declare(strict_types=1);

namespace App\Services\Sorting;

final class BubbleSortService
{
    /** Каждые N проходов сортировки отправляется обновление прогресса в интерфейс */
    public const int PROGRESS_REPORT_INTERVAL = 50;

    /** Максимум проходов за один HTTP-шаг Livewire (дополнительный предел к времени) */
    public const int STEP_BATCH_PASSES = 25;

    /** Максимальная длительность одного HTTP-шага, чтобы «Остановить» успевало сработать */
    public const float STEP_MAX_SECONDS = 1.5;

    private const int CANCEL_CHECK_INTERVAL = 1_000;

    /**
     * Выполняет ограниченное число проходов сортировки для пошагового выполнения.
     *
     * @param list<int> $data
     * @return array{completed: bool, cancelled: bool, pass: int, upper_bound: int}
     */
    public function sortBatch(
        array &$data,
        int &$pass,
        int &$upperBound,
        int $maxPasses,
        ?callable $shouldCancel = null,
    ): array {
        $passesDone = 0;
        $deadline = microtime(true) + self::STEP_MAX_SECONDS;

        while ($upperBound > 0 && $passesDone < $maxPasses) {
            if ($this->shouldCancel($shouldCancel) || microtime(true) >= $deadline) {
                if ($this->shouldCancel($shouldCancel)) {
                    return [
                        'completed' => false,
                        'cancelled' => true,
                        'pass' => $pass,
                        'upper_bound' => max(0, $upperBound),
                    ];
                }

                break;
            }

            $pass++;
            $passesDone++;
            $lastSwapIndex = 0;

            for ($index = 0; $index < $upperBound; $index++) {
                if ($index > 0 && $index % self::CANCEL_CHECK_INTERVAL === 0) {
                    if ($this->shouldCancel($shouldCancel)) {
                        return [
                            'completed' => false,
                            'cancelled' => true,
                            'pass' => $pass,
                            'upper_bound' => max(0, $upperBound),
                        ];
                    }

                    if (microtime(true) >= $deadline) {
                        break 2;
                    }
                }

                if ($data[$index] > $data[$index + 1]) {
                    $temporary = $data[$index];
                    $data[$index] = $data[$index + 1];
                    $data[$index + 1] = $temporary;
                    $lastSwapIndex = $index + 1;
                }
            }

            $upperBound = $lastSwapIndex - 1;
        }

        return [
            'completed' => $upperBound <= 0,
            'cancelled' => false,
            'pass' => $pass,
            'upper_bound' => max(0, $upperBound),
        ];
    }

    private function shouldCancel(?callable $shouldCancel): bool
    {
        return $shouldCancel !== null && $shouldCancel();
    }
}
