<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerSeasonMemberships;

use App\Filament\Resources\PlayerSeasonMemberships\Pages\CreatePlayerSeasonMembership;
use App\Filament\Resources\PlayerSeasonMemberships\Pages\EditPlayerSeasonMembership;
use App\Filament\Resources\PlayerSeasonMemberships\Pages\ListPlayerSeasonMemberships;
use App\Filament\Resources\PlayerSeasonMemberships\Schemas\PlayerSeasonMembershipForm;
use App\Filament\Resources\PlayerSeasonMemberships\Tables\PlayerSeasonMembershipsTable;
use App\Models\PlayerSeasonMembership;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlayerSeasonMembershipResource extends Resource
{
    protected static ?string $model = PlayerSeasonMembership::class;

    protected static ?string $navigationParentItem = 'Задача 2';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Составы';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'запись состава';

    protected static ?string $pluralModelLabel = 'Составы';

    protected static ?string $slug = 'task-2/memberships';

    public static function form(Schema $schema): Schema
    {
        return PlayerSeasonMembershipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlayerSeasonMembershipsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlayerSeasonMemberships::route('/'),
            'create' => CreatePlayerSeasonMembership::route('/create'),
            'edit' => EditPlayerSeasonMembership::route('/{record}/edit'),
        ];
    }

    /** @return Builder<PlayerSeasonMembership> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'season',
                'player.translations',
                'club.translations',
            ]);
    }
}
