<?php

declare(strict_types=1);

namespace App\Filament\Resources\Seasons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SeasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label('Сезон')
                    ->placeholder('2025-2026')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Уникальный идентификатор сезона, например 2025-2026.'),
                Grid::make(2)
                    ->schema([
                        DatePicker::make('starts_at')
                            ->label('Дата начала')
                            ->required()
                            ->native(false),
                        DatePicker::make('ends_at')
                            ->label('Дата окончания')
                            ->required()
                            ->after('starts_at')
                            ->native(false),
                    ]),
                Toggle::make('is_active')
                    ->label('Активный сезон')
                    ->helperText('В системе может быть только один активный сезон.')
                    ->default(false),
            ]);
    }
}
