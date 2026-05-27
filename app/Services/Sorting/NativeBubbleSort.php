<?php

declare(strict_types=1);

namespace App\Services\Sorting;

use RuntimeException;

final class NativeBubbleSort
{
    private const string EXECUTABLE_BASENAME = 'bubble_sort';

    /** Проверяет, собран ли нативный исполняемый файл пузырьковой сортировки */
    public function isAvailable(): bool
    {
        $path = $this->executablePath();

        return $path !== null && is_executable($path);
    }

    /** Причина недоступности нативного движка */
    public function unavailableReason(): ?string
    {
        if ($this->isAvailable()) {
            return null;
        }

        if ($this->executablePath() === null) {
            return 'Выполните: php artisan task1:build-native';
        }

        return 'Файл native/bubble_sort не исполняемый. Выполните: chmod +x native/bubble_sort';
    }

    /**
     * Пакет проходов пузырьковой сортировки по бинарному файлу int32.
     *
     * @return array{completed: bool, pass: int, upper_bound: int}
     */
    public function sortFileBatch(
        string $path,
        int &$pass,
        int &$upperBound,
        int $maxPasses,
    ): array {
        $binary = $this->executablePath();

        if ($binary === null) {
            throw new RuntimeException('Исполняемый файл native/bubble_sort не найден. Запустите: php artisan task1:build-native');
        }

        $command = sprintf(
            '%s %s %d %d %d 2>&1',
            escapeshellarg($binary),
            escapeshellarg($path),
            $maxPasses,
            $pass,
            $upperBound,
        );

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || $output === []) {
            throw new RuntimeException(
                'Ошибка нативной сортировки: ' . implode("\n", $output),
            );
        }

        $parts = sscanf($output[0], '%d %d %d');

        if ($parts === null || count($parts) < 3) {
            throw new RuntimeException('Некорректный ответ нативной сортировки: ' . $output[0]);
        }

        [$completedFlag, $newPass, $newUpperBound] = $parts;
        $pass = (int) $newPass;
        $upperBound = max(0, (int) $newUpperBound);

        return [
            'completed' => (int) $completedFlag === 1,
            'pass' => $pass,
            'upper_bound' => $upperBound,
        ];
    }

    public function executablePath(): ?string
    {
        $path = base_path('native/' . self::EXECUTABLE_BASENAME);

        return is_file($path) ? $path : null;
    }

    /** Собирает native/bubble_sort из bubble_sort.c */
    public function build(): string
    {
        $source = base_path('native/bubble_sort.c');
        $output = base_path('native/' . self::EXECUTABLE_BASENAME);

        if (! is_file($source)) {
            throw new RuntimeException('Исходник native/bubble_sort.c не найден.');
        }

        $command = sprintf(
            'clang -O3 %s -o %s 2>&1',
            escapeshellarg($source),
            escapeshellarg($output),
        );

        exec($command, $lines, $exitCode);

        if ($exitCode !== 0 || ! is_file($output)) {
            throw new RuntimeException('Сборка не удалась: ' . implode("\n", $lines));
        }

        chmod($output, 0755);

        return $output;
    }
}
