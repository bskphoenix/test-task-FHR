<?php

declare(strict_types=1);

namespace App\Services\UserExport;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

final class UserCsvExportSessionStore
{
    private const int TTL_SECONDS = 3600;

    private function cache(): Repository
    {
        return Cache::store('file');
    }

    /** Создаёт сессию выгрузки и CSV-файл с заголовком */
    public function create(string $exportId, int $totalCount, string $ownerKey): string
    {
        $filePath = $this->filePath($exportId);
        $this->ensureDirectoryExists();

        $handle = fopen($filePath, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('Не удалось создать CSV-файл выгрузки.');
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['Фамилия', 'Имя', 'Телефон', 'E-mail']);
        fclose($handle);

        $this->cache()->put($this->sessionKey($exportId), [
            'owner_key' => $ownerKey,
            'total_count' => $totalCount,
            'processed_count' => 0,
            'last_id' => 0,
            'started_at' => microtime(true),
            'file_path' => $filePath,
            'completed' => false,
        ], self::TTL_SECONDS);

        return $filePath;
    }

    /**
     * @return array{
     *     owner_key: string,
     *     total_count: int,
     *     processed_count: int,
     *     last_id: int,
     *     started_at: float,
     *     file_path: string,
     *     completed: bool,
     * }|null
     */
    public function get(string $exportId): ?array
    {
        $session = $this->cache()->get($this->sessionKey($exportId));

        return is_array($session) ? $session : null;
    }

    /** Обновляет прогресс сессии выгрузки */
    public function update(string $exportId, int $processedCount, int $lastId, bool $completed = false): void
    {
        $session = $this->get($exportId);

        if ($session === null) {
            return;
        }

        $session['processed_count'] = $processedCount;
        $session['last_id'] = $lastId;
        $session['completed'] = $completed;

        $this->cache()->put($this->sessionKey($exportId), $session, self::TTL_SECONDS);
    }

    /** Возвращает путь к CSV, если сессия принадлежит владельцу и выгрузка завершена */
    public function getDownloadPath(string $exportId, string $ownerKey): ?string
    {
        $session = $this->get($exportId);

        if ($session === null || $session['owner_key'] !== $ownerKey || ! $session['completed']) {
            return null;
        }

        $filePath = $session['file_path'];

        return is_file($filePath) ? $filePath : null;
    }

    /** Удаляет сессию и CSV-файл */
    public function forget(string $exportId): void
    {
        $session = $this->get($exportId);

        if ($session !== null && is_file($session['file_path'])) {
            unlink($session['file_path']);
        }

        $this->cache()->forget($this->sessionKey($exportId));
    }

    private function filePath(string $exportId): string
    {
        return $this->directoryPath() . DIRECTORY_SEPARATOR . $exportId . '.csv';
    }

    private function directoryPath(): string
    {
        return storage_path('app/task3-exports');
    }

    private function ensureDirectoryExists(): void
    {
        $directory = $this->directoryPath();

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function sessionKey(string $exportId): string
    {
        return "task3-export:session:{$exportId}";
    }
}
