<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\Database\DatabaseDumpService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Throwable;

class Task2ExportPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Выгрузка';

    protected static ?string $navigationParentItem = 'Задача 2';

    protected static ?string $title = 'Выгрузка базы данных';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'task-2/export';

    protected string $view = 'filament.pages.task2-export-page';

    public function getSubheading(): ?string
    {
        return 'SQL-дамп со структурой таблиц и данными текущей базы.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadDump')
                ->label('Скачать выгрузку')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => $this->downloadDump()),
        ];
    }

    /** Скачивает SQL-дамп базы данных */
    public function downloadDump(): mixed
    {
        try {
            $dump = app(DatabaseDumpService::class)->generate();
            $filename = 'vygruzka-bazy-'.now()->format('Y-m-d-His').'.sql';

            return response()->streamDownload(
                static function () use ($dump): void {
                    echo $dump;
                },
                $filename,
                ['Content-Type' => 'application/sql; charset=UTF-8'],
            );
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Не удалось сформировать выгрузку')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }
}
