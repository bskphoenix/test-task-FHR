<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clubs;

use App\Filament\Resources\Clubs\Pages\CreateClub;
use App\Filament\Resources\Clubs\Pages\EditClub;
use App\Filament\Resources\Clubs\Pages\ListClubs;
use App\Filament\Resources\Clubs\Schemas\ClubForm;
use App\Filament\Resources\Clubs\Tables\ClubsTable;
use App\Models\Club;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClubResource extends Resource
{
    protected static ?string $model = Club::class;

    protected static ?string $navigationParentItem = 'Задача 2';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Клубы';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'клуб';

    protected static ?string $pluralModelLabel = 'Клубы';

    protected static ?string $slug = 'task-2/clubs';

    public static function form(Schema $schema): Schema
    {
        return ClubForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClubsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClubs::route('/'),
            'create' => CreateClub::route('/create'),
            'edit' => EditClub::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Club> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('translations');
    }
}
