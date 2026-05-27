<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerSeasonMemberships\Tables;

use App\Filament\Support\RosterLabels;
use App\Models\PlayerSeasonMembership;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlayerSeasonMembershipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('joined_at', 'desc')
            ->columns([
                TextColumn::make('season.slug')
                    ->label('Сезон')
                    ->sortable(),
                TextColumn::make('player_name')
                    ->label('Игрок')
                    ->getStateUsing(
                        fn (PlayerSeasonMembership $record): string => RosterLabels::playerName($record->player),
                    ),
                TextColumn::make('club_name')
                    ->label('Клуб')
                    ->getStateUsing(
                        fn (PlayerSeasonMembership $record): string => RosterLabels::clubName($record->club),
                    ),
                TextColumn::make('jersey_number')
                    ->label('№')
                    ->sortable(),
                TextColumn::make('joined_at')
                    ->label('С')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('left_at')
                    ->label('По')
                    ->date('d.m.Y')
                    ->placeholder('сейчас')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->getStateUsing(fn (PlayerSeasonMembership $record): bool => $record->isActive()),
            ])
            ->filters([
                SelectFilter::make('season_id')
                    ->label('Сезон')
                    ->relationship('season', 'slug'),
                SelectFilter::make('club_id')
                    ->label('Клуб')
                    ->relationship('club', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record): string => RosterLabels::clubName($record),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
