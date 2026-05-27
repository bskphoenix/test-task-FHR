<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\ExportUser;
use App\Services\UserExport\UserCsvExportProgress;
use App\Services\UserExport\UserCsvExportRunner;
use App\Services\UserExport\UserCsvExportSessionStore;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class Task3ExportPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Выгрузка';

    protected static ?string $navigationParentItem = 'Задача 3';

    protected static ?string $title = 'Выгрузка пользователей';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'task-3/export';

    protected string $view = 'filament.pages.task3-export-page';

    public bool $isExporting = false;

    public ?string $exportId = null;

    /** @var array<string, mixed>|null */
    public ?array $progress = null;

    public ?string $downloadUrl = null;

    public int $totalUsers = 0;

    public function mount(): void
    {
        $this->refreshTotalUsers();
    }

    /** Запускает пошаговую выгрузку пользователей */
    public function startExport(
        UserCsvExportRunner $exportRunner,
        UserCsvExportSessionStore $sessionStore,
    ): void {
        set_time_limit(0);

        if ($this->isExporting) {
            return;
        }

        if ($this->totalUsers === 0) {
            Notification::make()
                ->title('Нет данных для выгрузки')
                ->body('Выполните команду php artisan task3:setup для заполнения тестовой базы.')
                ->warning()
                ->send();

            return;
        }

        if ($this->exportId !== null) {
            $sessionStore->forget($this->exportId);
        }

        $this->exportId = (string) Str::uuid();
        $this->isExporting = true;
        $this->downloadUrl = null;

        try {
            $this->progress = $exportRunner
                ->start($this->exportId, $sessionStore, $this->resolveOwnerKey())
                ->toArray();
        } catch (\Throwable $exception) {
            $this->isExporting = false;
            $this->exportId = null;
            $this->progress = null;

            Notification::make()
                ->title('Не удалось начать выгрузку')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->dispatch('task3-export-step');
    }

    /** Выполняет один шаг выгрузки */
    #[On('task3-export-step')]
    public function processExportStep(
        UserCsvExportRunner $exportRunner,
        UserCsvExportSessionStore $sessionStore,
    ): void {
        if (! $this->isExporting || $this->exportId === null) {
            return;
        }

        set_time_limit(0);

        $outcome = $exportRunner->processStep($this->exportId, $sessionStore);
        $this->progress = $outcome['progress']->toArray();

        if ($outcome['continue']) {
            $this->dispatch('task3-export-step');

            return;
        }

        $this->isExporting = false;

        if ($outcome['progress']->status === UserCsvExportProgress::STATUS_COMPLETED) {
            $this->downloadUrl = route('task3.download', ['exportId' => $this->exportId]);
            $this->progress['download_url'] = $this->downloadUrl;

            $this->dispatch('task3-export-complete', downloadUrl: $this->downloadUrl);

            Notification::make()
                ->title('Выгрузка завершена')
                ->body(sprintf(
                    'Сформирован CSV-файл с %s пользователями.',
                    number_format($outcome['progress']->processedCount, 0, ',', ' '),
                ))
                ->success()
                ->send();

            return;
        }

        $this->exportId = null;

        Notification::make()
            ->title('Ошибка выгрузки')
            ->body($outcome['progress']->errorMessage ?? 'Неизвестная ошибка.')
            ->danger()
            ->send();
    }

    /** Обновляет количество пользователей в тестовой базе */
    public function refreshTotalUsers(): void
    {
        try {
            $this->totalUsers = ExportUser::query()->count();
        } catch (\Throwable) {
            $this->totalUsers = 0;
        }
    }

    private function resolveOwnerKey(): string
    {
        $userId = auth()->id();

        return $userId !== null ? 'user:' . $userId : 'session:' . session()->getId();
    }
}
