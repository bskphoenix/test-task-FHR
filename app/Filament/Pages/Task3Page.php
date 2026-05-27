<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Task3Page extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBarsArrowDown;

    protected static ?string $navigationLabel = 'Задача 3';

    protected static ?string $title = 'Задача 3: Выгрузка пользователей';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'task-3';

    protected string $view = 'filament.pages.task3-page';

    public function getSubheading(): string|Htmlable|null
    {
        return 'Отдельная тестовая база с более чем 500 000 пользователей и выгрузка в CSV.';
    }
}
