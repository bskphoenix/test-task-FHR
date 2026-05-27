<?php

declare(strict_types=1);

namespace App\Filament\Resources\Players\Tables;

use App\Models\Player;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlayersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name_ru')
                    ->label('ФИО (рус.)')
                    ->getStateUsing(fn (Player $record): string => $record->translate('ru')?->full_name ?? '—')
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('translations', function ($query) use ($search): void {
                            $query->where('locale', 'ru')
                                ->where('full_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('full_name_en')
                    ->label('ФИО (англ.)')
                    ->getStateUsing(fn (Player $record): string => $record->translate('en')?->full_name ?? '—'),
                TextColumn::make('weight_kg')
                    ->label('Вес, кг')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('height_cm')
                    ->label('Рост, см')
                    ->sortable(),
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
