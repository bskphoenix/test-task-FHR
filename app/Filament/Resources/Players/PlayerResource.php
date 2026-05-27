<?php

declare(strict_types=1);

namespace App\Filament\Resources\Players;

use App\Filament\Resources\Players\Pages\CreatePlayer;
use App\Filament\Resources\Players\Pages\EditPlayer;
use App\Filament\Resources\Players\Pages\ListPlayers;
use App\Filament\Resources\Players\Schemas\PlayerForm;
use App\Filament\Resources\Players\Tables\PlayersTable;
use App\Models\Player;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    protected static ?string $navigationParentItem = 'Задача 2';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Игроки';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'игрок';

    protected static ?string $pluralModelLabel = 'Игроки';

    protected static ?string $slug = 'task-2/players';

    public static function form(Schema $schema): Schema
    {
        return PlayerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlayersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlayers::route('/'),
            'create' => CreatePlayer::route('/create'),
            'edit' => EditPlayer::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Player> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('translations');
    }
}
