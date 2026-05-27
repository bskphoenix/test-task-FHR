<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clubs\Tables;

use App\Models\Club;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClubsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ru')
                    ->label('Название (рус.)')
                    ->getStateUsing(fn (Club $record): string => $record->translate('ru')?->name ?? '—'),
                TextColumn::make('name_en')
                    ->label('Название (англ.)')
                    ->getStateUsing(fn (Club $record): string => $record->translate('en')?->name ?? '—'),
                TextColumn::make('city_ru')
                    ->label('Город (рус.)')
                    ->getStateUsing(fn (Club $record): string => $record->translate('ru')?->city ?? '—'),
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
