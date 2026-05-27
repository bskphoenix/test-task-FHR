<?php

declare(strict_types=1);

namespace App\Services\UserExport;

use App\Models\ExportUser;
use Throwable;

final class UserCsvExportRunner
{
    public const int CHUNK_SIZE = 5_000;

    /** Инициализирует пошаговую выгрузку пользователей в CSV */
    public function start(string $exportId, UserCsvExportSessionStore $sessionStore, string $ownerKey): UserCsvExportProgress
    {
        $totalCount = ExportUser::query()->count();

        if ($totalCount === 0) {
            throw new \RuntimeException('В тестовой базе нет пользователей. Выполните php artisan task3:setup.');
        }

        $sessionStore->create($exportId, $totalCount, $ownerKey);

        return $this->makeProgress(
            status: UserCsvExportProgress::STATUS_RUNNING,
            stageLabel: 'Подготовка выгрузки...',
            processedCount: 0,
            totalCount: $totalCount,
            startedAt: microtime(true),
        );
    }

    /**
     * Обрабатывает один шаг выгрузки
     *
     * @return array{continue: bool, progress: UserCsvExportProgress}
     */
    public function processStep(string $exportId, UserCsvExportSessionStore $sessionStore): array
    {
        $session = $sessionStore->get($exportId);

        if ($session === null) {
            return [
                'continue' => false,
                'progress' => $this->makeProgress(
                    status: UserCsvExportProgress::STATUS_FAILED,
                    stageLabel: 'Сессия выгрузки не найдена',
                    processedCount: 0,
                    totalCount: 0,
                    startedAt: microtime(true),
                    errorMessage: 'Сессия выгрузки не найдена.',
                ),
            ];
        }

        $startedAt = (float) $session['started_at'];
        $totalCount = (int) $session['total_count'];
        $processedCount = (int) $session['processed_count'];
        $lastId = (int) $session['last_id'];
        $filePath = $session['file_path'];

        try {
            $users = ExportUser::query()
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->limit(self::CHUNK_SIZE)
                ->get(['id', 'last_name', 'first_name', 'phone', 'email']);

            if ($users->isEmpty()) {
                $sessionStore->update($exportId, $processedCount, $lastId, completed: true);

                return [
                    'continue' => false,
                    'progress' => $this->makeProgress(
                        status: UserCsvExportProgress::STATUS_COMPLETED,
                        stageLabel: 'Выгрузка завершена',
                        processedCount: $processedCount,
                        totalCount: $totalCount,
                        startedAt: $startedAt,
                    ),
                ];
            }

            $handle = fopen($filePath, 'ab');

            if ($handle === false) {
                throw new \RuntimeException('Не удалось открыть CSV-файл для записи.');
            }

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->last_name,
                    $user->first_name,
                    $user->phone,
                    $user->email,
                ]);
            }

            fclose($handle);

            $processedCount += $users->count();
            $lastId = (int) $users->last()->id;
            $isCompleted = $processedCount >= $totalCount;

            $sessionStore->update($exportId, $processedCount, $lastId, completed: $isCompleted);

            if ($isCompleted) {
                return [
                    'continue' => false,
                    'progress' => $this->makeProgress(
                        status: UserCsvExportProgress::STATUS_COMPLETED,
                        stageLabel: 'Выгрузка завершена',
                        processedCount: $processedCount,
                        totalCount: $totalCount,
                        startedAt: $startedAt,
                    ),
                ];
            }

            return [
                'continue' => true,
                'progress' => $this->makeProgress(
                    status: UserCsvExportProgress::STATUS_RUNNING,
                    stageLabel: sprintf(
                        'Выгружено %s из %s пользователей',
                        number_format($processedCount, 0, ',', ' '),
                        number_format($totalCount, 0, ',', ' '),
                    ),
                    processedCount: $processedCount,
                    totalCount: $totalCount,
                    startedAt: $startedAt,
                ),
            ];
        } catch (Throwable $exception) {
            return [
                'continue' => false,
                'progress' => $this->makeProgress(
                    status: UserCsvExportProgress::STATUS_FAILED,
                    stageLabel: 'Ошибка выгрузки',
                    processedCount: $processedCount,
                    totalCount: $totalCount,
                    startedAt: $startedAt,
                    errorMessage: $exception->getMessage(),
                ),
            ];
        }
    }

    private function makeProgress(
        string $status,
        string $stageLabel,
        int $processedCount,
        int $totalCount,
        float $startedAt,
        ?string $errorMessage = null,
    ): UserCsvExportProgress {
        $progressPercent = $totalCount > 0
            ? min(100, ($processedCount / $totalCount) * 100)
            : 0;

        return new UserCsvExportProgress(
            status: $status,
            stageLabel: $stageLabel,
            progressPercent: $progressPercent,
            processedCount: $processedCount,
            totalCount: $totalCount,
            elapsedSeconds: max(0, microtime(true) - $startedAt),
            memoryCurrentBytes: memory_get_usage(true),
            memoryPeakBytes: memory_get_peak_usage(true),
            errorMessage: $errorMessage,
        );
    }
}
