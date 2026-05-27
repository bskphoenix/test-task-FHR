<?php

declare(strict_types=1);

namespace App\Filament\Resources\Seasons;

use App\Filament\Resources\Seasons\Pages\CreateSeason;
use App\Filament\Resources\Seasons\Pages\EditSeason;
use App\Filament\Resources\Seasons\Pages\ListSeasons;
use App\Filament\Resources\Seasons\Schemas\SeasonForm;
use App\Filament\Resources\Seasons\Tables\SeasonsTable;
use App\Models\Season;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeasonResource extends Resource
{
    protected static ?string $model = Season::class;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static ?string $navigationParentItem = 'Задача 2';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Сезоны';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'сезон';

    protected static ?string $pluralModelLabel = 'Сезоны';

    protected static ?string $slug = 'task-2/seasons';

    public static function form(Schema $schema): Schema
    {
        return SeasonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeasonsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeasons::route('/'),
            'create' => CreateSeason::route('/create'),
            'edit' => EditSeason::route('/{record}/edit'),
        ];
    }
}
