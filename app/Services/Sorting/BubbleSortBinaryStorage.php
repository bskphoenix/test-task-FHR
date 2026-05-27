<?php

declare(strict_types=1);

namespace App\Services\Sorting;

final class BubbleSortBinaryStorage
{
    /**
     * Записывает массив целых чисел в бинарный файл (4 байта на элемент).
     *
     * @param list<int> $data
     */
    public function write(string $path, array $data): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException("Не удалось записать файл: {$path}");
        }

        $chunkSize = 10_000;

        for ($offset = 0, $total = count($data); $offset < $total; $offset += $chunkSize) {
            $chunk = array_slice($data, $offset, $chunkSize);
            $packed = pack('l*', ...$chunk);

            if ($packed === false || fwrite($handle, $packed) !== strlen($packed)) {
                fclose($handle);

                throw new \RuntimeException("Ошибка записи данных: {$path}");
            }
        }

        fclose($handle);
    }

    /** Экспортирует бинарный int32-файл в CSV (колонка value) */
    public function exportToCsv(string $binaryPath, string $csvPath): void
    {
        if (! is_file($binaryPath)) {
            throw new \RuntimeException("Исходный файл не найден: {$binaryPath}");
        }

        $directory = dirname($csvPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $input = fopen($binaryPath, 'rb');

        if ($input === false) {
            throw new \RuntimeException("Не удалось прочитать файл: {$binaryPath}");
        }

        $output = fopen($csvPath, 'wb');

        if ($output === false) {
            fclose($input);

            throw new \RuntimeException("Не удалось создать CSV: {$csvPath}");
        }

        fwrite($output, "value\n");

        while (($packed = fread($input, 4)) !== false && strlen($packed) === 4) {
            $unpacked = unpack('l', $packed);
            $value = is_array($unpacked) ? (int) $unpacked[1] : 0;

            if (fwrite($output, $value . "\n") === false) {
                fclose($input);
                fclose($output);

                throw new \RuntimeException("Ошибка записи CSV: {$csvPath}");
            }
        }

        fclose($input);
        fclose($output);
    }

    /** Проверяет, отсортирован ли бинарный файл по возрастанию */
    public function isSortedFile(string $path): bool
    {
        if (! is_file($path)) {
            return false;
        }

        $size = filesize($path);

        if ($size === false || $size < 8 || $size % 4 !== 0) {
            return false;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        $previous = null;

        while (! feof($handle)) {
            $packed = fread($handle, 4);

            if ($packed === false || strlen($packed) !== 4) {
                break;
            }

            $unpacked = unpack('l', $packed);
            $value = is_array($unpacked) ? (int) $unpacked[1] : 0;

            if ($previous !== null && $previous > $value) {
                fclose($handle);

                return false;
            }

            $previous = $value;
        }

        fclose($handle);

        return true;
    }
}
