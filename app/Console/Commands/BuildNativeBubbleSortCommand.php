<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Sorting\NativeBubbleSort;
use Illuminate\Console\Command;

class BuildNativeBubbleSortCommand extends Command
{
    protected $signature = 'task1:build-native';

    protected $description = 'Собирает native/bubble_sort (пузырёк на C, требуется clang)';

    /** Собирает исполняемый файл пузырьковой сортировки */
    public function handle(NativeBubbleSort $nativeBubbleSort): int
    {
        try {
            $path = $nativeBubbleSort->build();
        } catch (\Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Готово: ' . $path);

        return self::SUCCESS;
    }
}
