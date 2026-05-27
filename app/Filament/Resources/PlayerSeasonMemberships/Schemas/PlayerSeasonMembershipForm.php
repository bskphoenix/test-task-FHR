<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerSeasonMemberships\Schemas;

use App\Filament\Support\RosterLabels;
use App\Models\Club;
use App\Models\Player;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PlayerSeasonMembershipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('season_id')
                            ->label('Сезон')
                            ->relationship('season', 'slug')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('player_id')
                            ->label('Игрок')
                            ->relationship(
                                name: 'player',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn ($query) => $query->with('translations'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Player $record): string => RosterLabels::playerName($record),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('club_id')
                            ->label('Клуб')
                            ->relationship(
                                name: 'club',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn ($query) => $query->with('translations'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Club $record): string => RosterLabels::clubName($record),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('jersey_number')
                            ->label('Игровой номер')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(999)
                            ->required(),
                        DatePicker::make('joined_at')
                            ->label('Дата начала')
                            ->required()
                            ->native(false),
                        DatePicker::make('left_at')
                            ->label('Дата окончания')
                            ->helperText('Оставьте пустым, если игрок сейчас в клубе.')
                            ->native(false),
                    ]),
            ]);
    }
}
