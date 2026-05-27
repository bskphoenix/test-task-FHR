<?php

declare(strict_types=1);

namespace App\Services\Sorting;

final class BubbleSortResultStore
{
    public const string ORIGINAL_CSV = 'original.csv';

    public const string SORTED_CSV = 'sorted.csv';

    public const string ORIGINAL_BIN = 'original.bin';

    public const string SORTED_BIN = 'sorted.bin';

    /** Каталог результатов по умолчанию */
    public function directory(): string
    {
        return storage_path('app/bubble-sort-results');
    }

    /** Создаёт каталог результатов, если его ещё нет */
    public function ensureDirectory(?string $directory = null): string
    {
        $directory ??= $this->directory();

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory;
    }

    public function originalCsvPath(?string $directory = null): string
    {
        return $this->path(self::ORIGINAL_CSV, $directory);
    }

    public function sortedCsvPath(?string $directory = null): string
    {
        return $this->path(self::SORTED_CSV, $directory);
    }

    public function originalBinPath(?string $directory = null): string
    {
        return $this->path(self::ORIGINAL_BIN, $directory);
    }

    public function sortedBinPath(?string $directory = null): string
    {
        return $this->path(self::SORTED_BIN, $directory);
    }

    /** Доступны ли CSV-файлы для скачивания */
    public function downloadsAvailable(?string $directory = null): bool
    {
        return is_file($this->originalCsvPath($directory))
            && is_file($this->sortedCsvPath($directory));
    }

    /** CSV созданы не раньше указанного момента запуска */
    public function downloadsAvailableAfter(float $startedAt, ?string $directory = null): bool
    {
        if (! $this->downloadsAvailable($directory)) {
            return false;
        }

        $since = (int) floor($startedAt) - 1;

        return filemtime($this->originalCsvPath($directory)) >= $since
            && filemtime($this->sortedCsvPath($directory)) >= $since;
    }

    /** Удаляет CSV-результаты перед новым запуском */
    public function clearDownloadFiles(?string $directory = null): void
    {
        foreach ([self::ORIGINAL_CSV, self::SORTED_CSV] as $filename) {
            $path = $this->path($filename, $directory);

            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function path(string $filename, ?string $directory = null): string
    {
        return ($directory ?? $this->directory()) . DIRECTORY_SEPARATOR . $filename;
    }
}
