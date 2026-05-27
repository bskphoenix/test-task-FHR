<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\Sorting\BubbleSortResultStore;
use App\Services\Sorting\BubbleSortService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\Process\Process;

class Task1SortPage extends Page
{
    private const string LOG_PATH = 'logs/task1-sort.log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlay;

    protected static ?string $navigationLabel = 'Сортировка';

    protected static ?string $navigationParentItem = 'Задача 1';

    protected static ?string $title = 'Задача 1 - Сортировка';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'task-1/sort';

    protected string $view = 'filament.pages.task1-sort-page';

    public bool $nativeReady = false;

    public ?string $nativeSetupHint = null;

    public ?string $lastRunLogPath = null;

    public string $lastRunLogContent = '';

    public bool $downloadsReady = false;

    public bool $sortInProgress = false;

    public ?float $sortStartedAt = null;

    public function mount(BubbleSortService $bubbleSortService): void
    {
        $this->nativeReady = $bubbleSortService->isAvailable();
        $this->nativeSetupHint = $this->nativeReady
            ? null
            : $bubbleSortService->unavailableReason();
        $this->lastRunLogPath = storage_path(self::LOG_PATH);
        $this->lastRunLogContent = 'Нажмите «Обновить лог», чтобы прочитать файл.';
        $this->downloadsReady = false;
        $this->sortInProgress = false;
    }

    /** Запускает консольную сортировку в фоне */
    public function runConsoleSort(
        BubbleSortService $bubbleSortService,
        BubbleSortResultStore $resultStore,
    ): void {
        if (! $bubbleSortService->isAvailable()) {
            Notification::make()
                ->title('Нативная сортировка недоступна')
                ->body($bubbleSortService->unavailableReason() ?? 'Запустите php artisan task1:build-native')
                ->danger()
                ->send();

            return;
        }

        $logPath = storage_path(self::LOG_PATH);

        if (! is_dir(dirname($logPath))) {
            mkdir(dirname($logPath), 0755, true);
        }

        file_put_contents($logPath, '');
        $resultStore->clearDownloadFiles();
        $this->sortStartedAt = microtime(true);

        try {
            $this->startBackgroundSort($logPath);
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Не удалось запустить сортировку')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->lastRunLogPath = $logPath;
        $this->lastRunLogContent = 'Лог очищен. Команда запущена — ожидайте завершения...';
        $this->downloadsReady = false;
        $this->sortInProgress = true;

        Notification::make()
            ->title('Сортировка запущена')
            ->body('Процесс работает в фоне. Лог: ' . $logPath)
            ->success()
            ->send();
    }

    /** Запускает artisan task1:sort в фоне, не блокируя HTTP-запрос */
    private function startBackgroundSort(string $logPath): void
    {
        $process = Process::fromShellCommandline(
            sprintf(
                'nohup %s artisan task1:sort > %s 2>&1 &',
                escapeshellarg(PHP_BINARY),
                escapeshellarg($logPath),
            ),
            base_path(),
        );

        $process->setTimeout(null);
        $process->start();
    }

    /** Обновляет текст последнего консольного лога */
    public function refreshConsoleSortLog(BubbleSortResultStore $resultStore): void
    {
        $logPath = $this->lastRunLogPath ?? storage_path(self::LOG_PATH);

        if (! is_file($logPath)) {
            $this->lastRunLogContent = 'Файл лога пока не создан. Запустите сортировку или выполните команду в консоли.';

            return;
        }

        $content = $this->readLogTail($logPath);
        $this->lastRunLogContent = $content !== ''
            ? $content
            : 'Лог пока пуст.';

        if ($this->sortInProgress
            && $this->sortStartedAt !== null
            && $this->isSortLogCompleted($logPath)
            && $resultStore->downloadsAvailableAfter($this->sortStartedAt)) {
            $this->downloadsReady = true;
            $this->sortInProgress = false;
        }
    }

    /** Проверяет по логу, что сортировка успешно завершена */
    private function isSortLogCompleted(string $logPath): bool
    {
        $content = @file_get_contents($logPath);

        if ($content === false || $content === '') {
            return false;
        }

        return str_contains($content, 'Проверка: массив отсортирован по возрастанию.');
    }

    private function readLogTail(string $path, int $bytes = 12000): string
    {
        $size = filesize($path);

        if ($size === false || $size === 0) {
            return '';
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return 'Не удалось прочитать файл лога.';
        }

        fseek($handle, max(0, $size - $bytes));
        $content = (string) fread($handle, min($bytes, $size));
        fclose($handle);

        $content = preg_replace('/\e\[[\d;]*[A-Za-z]/', '', $content) ?? $content;
        $content = str_replace("\r", "\n", $content);
        $lines = array_values(array_filter(explode("\n", $content), static fn (string $line): bool => trim($line) !== ''));

        return implode("\n", array_slice($lines, -80));
    }
}
