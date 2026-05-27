<?php

declare(strict_types=1);

namespace App\Services\Sorting;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class BubbleSortCancellation
{
    private const int TTL_SECONDS = 3600;

    private function cache(): Repository
    {
        return Cache::store('file');
    }

    /** Регистрирует новый запуск и сбрасывает флаг остановки */
    public function startRun(): string
    {
        $runId = (string) Str::uuid();

        $this->cache()->put($this->cancelKey($runId), false, self::TTL_SECONDS);

        return $runId;
    }

    /** Сохраняет активный запуск для текущего пользователя */
    public function registerActiveRun(string $runId, string $ownerKey): void
    {
        $this->cache()->put($this->activeRunKey($ownerKey), $runId, self::TTL_SECONDS);
    }

    /** Возвращает активный запуск пользователя */
    public function getActiveRunId(string $ownerKey): ?string
    {
        $runId = $this->cache()->get($this->activeRunKey($ownerKey));

        return is_string($runId) ? $runId : null;
    }

    /** Удаляет активный запуск пользователя */
    public function clearActiveRun(string $ownerKey): void
    {
        $this->cache()->forget($this->activeRunKey($ownerKey));
    }

    /** Запрашивает остановку текущего запуска */
    public function requestStop(string $runId): void
    {
        $this->cache()->put($this->cancelKey($runId), true, self::TTL_SECONDS);
    }

    /** Проверяет, запрошена ли остановка */
    public function shouldStop(string $runId): bool
    {
        return (bool) $this->cache()->get($this->cancelKey($runId), false);
    }

    /** Удаляет флаг остановки после завершения */
    public function clear(string $runId): void
    {
        $this->cache()->forget($this->cancelKey($runId));
    }

    private function cancelKey(string $runId): string
    {
        return "bubble-sort:cancel:{$runId}";
    }

    private function activeRunKey(string $ownerKey): string
    {
        return "bubble-sort:active-run:{$ownerKey}";
    }
}
