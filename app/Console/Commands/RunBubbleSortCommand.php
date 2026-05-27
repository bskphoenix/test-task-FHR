<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Sorting\BubbleSortBinaryStorage;
use App\Services\Sorting\BubbleSortResultStore;
use App\Services\Sorting\BubbleSortService;
use App\Services\Sorting\NativeBubbleSort;
use App\Services\Sorting\NumericArrayGenerator;
use Illuminate\Console\Command;

class RunBubbleSortCommand extends Command
{
    private const int MIN_COUNT = 2;

    private const int MAX_COUNT = 200_000;

    private const int PROGRESS_INTERVAL = 1_000;

    protected $signature = 'task1:sort
                            {--count=200000 : Количество элементов (2–200000)}';

    protected $description = 'Пузырьковая сортировка ';

    /** Запускает пузырьковую сортировку и сохраняет результат на диск */
    public function handle(
        NumericArrayGenerator $arrayGenerator,
        BubbleSortService $bubbleSortService,
        BubbleSortBinaryStorage $binaryStorage,
        BubbleSortResultStore $resultStore,
        NativeBubbleSort $nativeBubbleSort,
    ): int {
        set_time_limit(0);

        $this->ensureNativeBinary($nativeBubbleSort, $bubbleSortService);

        $count = max(self::MIN_COUNT, min(self::MAX_COUNT, (int) $this->option('count')));
        $outputDirectory = $resultStore->directory();

        $resultStore->ensureDirectory($outputDirectory);
        $resultStore->clearDownloadFiles($outputDirectory);

        $originalBinPath = $resultStore->originalBinPath($outputDirectory);
        $sortedBinPath = $resultStore->sortedBinPath($outputDirectory);
        $originalCsvPath = $resultStore->originalCsvPath($outputDirectory);
        $sortedCsvPath = $resultStore->sortedCsvPath($outputDirectory);

        $this->components->info('Задача 1: пузырьковая сортировка');
        $this->line('Каталог результатов: ' . $outputDirectory);
        $this->newLine();

        $generateStartedAt = microtime(true);
        $data = $arrayGenerator->generate($count);
        $generateDuration = microtime(true) - $generateStartedAt;
        $binaryStorage->write($originalBinPath, $data);
        copy($originalBinPath, $sortedBinPath);
        unset($data);

        $this->components->twoColumnDetail(
            'Генерация',
            sprintf('%s сек., память %s МБ', number_format($generateDuration, 2, ',', ' '), $this->formatMegabytes(memory_get_usage(true))),
        );

        $estimatedPasses = max(1, $count - 1);
        $progressBar = $this->output->createProgressBar($estimatedPasses);
        $progressBar->setFormat(
            " %current%/%max% проходов [%bar%] %percent:3s%%\n %message%\n Осталось: %remaining:6s%  Память: %memory:6s%",
        );
        $progressBar->setMessage('Сортировка...');
        $progressBar->start();

        $sortStartedAt = microtime(true);

        $outcome = $bubbleSortService->sortFileInPlace(
            $sortedBinPath,
            $count,
            onProgress: function (int $pass, int $estimated, float $elapsed) use ($progressBar): void {
                $progressBar->setProgress(min($pass, $progressBar->getMaxSteps()));
                $progressBar->setMessage(sprintf(
                    'Проход %s / ~%s, прошло %s сек.',
                    number_format($pass, 0, ',', ' '),
                    number_format($estimated, 0, ',', ' '),
                    number_format($elapsed, 1, ',', ' '),
                ));
            },
            progressInterval: self::PROGRESS_INTERVAL,
        );

        $sortDuration = microtime(true) - $sortStartedAt;

        $progressBar->finish();
        $this->newLine(2);

        $isSorted = $binaryStorage->isSortedFile($sortedBinPath);
        $peakMemory = memory_get_peak_usage(true);

        $binaryStorage->exportToCsv($originalBinPath, $originalCsvPath);
        $binaryStorage->exportToCsv($sortedBinPath, $sortedCsvPath);

        $this->components->info(sprintf(
            'Готово: %s проходов за %s сек., пик памяти %s МБ',
            number_format($outcome['pass'], 0, ',', ' '),
            number_format($outcome['duration_seconds'], 2, ',', ' '),
            $this->formatMegabytes($peakMemory),
        ));

        $this->components->twoColumnDetail('Исходный массив (CSV)', $originalCsvPath);
        $this->components->twoColumnDetail('Отсортированный массив (CSV)', $sortedCsvPath);

        if (! $isSorted) {
            $this->components->error('Проверка: массив не отсортирован.');

            return self::FAILURE;
        }

        $this->components->info('Проверка: массив отсортирован по возрастанию.');

        return self::SUCCESS;
    }

    private function ensureNativeBinary(NativeBubbleSort $nativeBubbleSort, BubbleSortService $bubbleSortService): void
    {
        if ($bubbleSortService->isAvailable()) {
            return;
        }

        try {
            $this->components->info('Сборка native/bubble_sort...');
            $nativeBubbleSort->build();
        } catch (\Throwable $exception) {
            $this->components->error($exception->getMessage());

            throw $exception;
        }
    }

    private function formatMegabytes(int $bytes): string
    {
        return number_format($bytes / 1024 / 1024, 2, ',', ' ');
    }
}
