<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Task2Page extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBarsArrowDown;

    protected static ?string $navigationLabel = 'Задача 2';

    protected static ?string $title = 'Задача 2: Игроки по сезонам';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'task-2';

    protected string $view = 'filament.pages.task2-page';

    public function getSubheading(): ?string
    {
        return 'Таблицы БД для хранения игроков по сезонам и выгрузка dump db.';
    }
}
