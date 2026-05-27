<?php

declare(strict_types=1);

namespace App\Filament\Resources\Seasons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeasonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('slug')
                    ->label('Сезон')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Начало')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Окончание')
                    ->date('d.m.Y')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                TextColumn::make('memberships_count')
                    ->label('Записей в составах')
                    ->counts('memberships'),
            ])
            ->filters([])
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
