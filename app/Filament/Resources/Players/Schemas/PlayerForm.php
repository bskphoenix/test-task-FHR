<?php

declare(strict_types=1);

namespace App\Filament\Resources\Players\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlayerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Параметры')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('weight_kg')
                                    ->label('Вес, кг')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(999.99)
                                    ->step(0.01),
                                TextInput::make('height_cm')
                                    ->label('Рост, см')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(999),
                            ]),
                    ]),
                Section::make('Русский')
                    ->schema([
                        TextInput::make('full_name_ru')
                            ->label('ФИО')
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Английский')
                    ->schema([
                        TextInput::make('full_name_en')
                            ->label('ФИО')
                            ->required()
                            ->maxLength(255),
                    ]),
            ]);
    }
}
