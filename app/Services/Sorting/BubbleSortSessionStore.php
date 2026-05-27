<?php

declare(strict_types=1);

namespace App\Services\Sorting;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

final class BubbleSortSessionStore
{
    private const int TTL_SECONDS = 3600;

    private function cache(): Repository
    {
        return Cache::store('file');
    }

    /**
     * @param list<int> $data
     * @param list<array{time: string, message: string}> $steps
     */
    public function create(
        string $runId,
        array $data,
        float $startedAt,
        int $progressReportInterval,
        array $steps,
        string $ownerKey,
    ): void {
        $this->writeData($runId, $data);
        $this->writeOriginalData($runId, $data);

        $this->cache()->put($this->sessionKey($runId), [
            'count' => count($data),
            'pass' => 0,
            'upper_bound' => max(0, count($data) - 1),
            'started_at' => $startedAt,
            'progress_report_interval' => max(1, $progressReportInterval),
            'steps' => $steps,
            'owner_key' => $ownerKey,
        ], self::TTL_SECONDS);
    }

    /**
     * @return array{
     *     data: list<int>,
     *     count: int,
     *     pass: int,
     *     upper_bound: int,
     *     started_at: float,
     *     progress_report_interval: int,
     *     steps: list<array{time: string, message: string}>,
     *     owner_key: string,
     * }|null
     */
    public function get(string $runId): ?array
    {
        $session = $this->cache()->get($this->sessionKey($runId));

        if (! is_array($session)) {
            return null;
        }

        $data = $this->readData($runId);

        if ($data === null) {
            return null;
        }

        $session['data'] = $data;

        return $session;
    }

    /**
     * @param list<int> $data
     * @param list<array{time: string, message: string}> $steps
     */
    public function update(
        string $runId,
        array $data,
        int $pass,
        int $upperBound,
        array $steps,
    ): void {
        $this->writeData($runId, $data);

        $session = $this->cache()->get($this->sessionKey($runId));

        if (! is_array($session)) {
            return;
        }

        $session['pass'] = $pass;
        $session['upper_bound'] = $upperBound;
        $session['steps'] = $steps;

        $this->cache()->put($this->sessionKey($runId), $session, self::TTL_SECONDS);
    }

    public function forget(string $runId): void
    {
        foreach ([$this->dataPath($runId), $this->originalDataPath($runId)] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->cache()->forget($this->sessionKey($runId));
    }

    /**
     * @return list<int>|null
     */
    public function getOriginalData(string $runId): ?array
    {
        return $this->readOriginalData($runId);
    }

    /**
     * @param list<int> $data
     */
    private function writeOriginalData(string $runId, array $data): void
    {
        $this->ensureDirectoryExists();

        file_put_contents(
            $this->originalDataPath($runId),
            serialize($data),
            LOCK_EX,
        );
    }

    /**
     * @return list<int>|null
     */
    private function readOriginalData(string $runId): ?array
    {
        $path = $this->originalDataPath($runId);

        if (! is_file($path)) {
            return null;
        }

        $serialized = file_get_contents($path);

        if ($serialized === false) {
            return null;
        }

        $data = unserialize($serialized, ['allowed_classes' => false]);

        return is_array($data) ? $data : null;
    }

    private function originalDataPath(string $runId): string
    {
        return $this->directoryPath() . DIRECTORY_SEPARATOR . $runId . '.original.dat';
    }

    /**
     * @param list<int> $data
     */
    private function writeData(string $runId, array $data): void
    {
        $this->ensureDirectoryExists();

        file_put_contents(
            $this->dataPath($runId),
            serialize($data),
            LOCK_EX,
        );
    }

    /**
     * @return list<int>|null
     */
    private function readData(string $runId): ?array
    {
        $path = $this->dataPath($runId);

        if (! is_file($path)) {
            return null;
        }

        $serialized = file_get_contents($path);

        if ($serialized === false) {
            return null;
        }

        $data = unserialize($serialized, ['allowed_classes' => false]);

        return is_array($data) ? $data : null;
    }

    private function dataPath(string $runId): string
    {
        return $this->directoryPath() . DIRECTORY_SEPARATOR . $runId . '.dat';
    }

    private function directoryPath(): string
    {
        return storage_path('app/bubble-sort-sessions');
    }

    private function ensureDirectoryExists(): void
    {
        $directory = $this->directoryPath();

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function sessionKey(string $runId): string
    {
        return "bubble-sort:session:{$runId}";
    }
}
