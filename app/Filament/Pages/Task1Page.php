<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Task1Page extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBarsArrowDown;

    protected static ?string $navigationLabel = 'Задача 1';

    protected static ?string $title = 'Задача 1: Пузырьковая сортировка';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'task-1';

    protected string $view = 'filament.pages.task1-page';

    public function getSubheading(): string|Htmlable|null
    {
        return 'Пузырьковая сортировка массива числовых данных от 200 тысяч элементов.';
    }
}
