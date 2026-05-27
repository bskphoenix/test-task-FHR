<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clubs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClubForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Русский')
                    ->schema([
                        TextInput::make('name_ru')
                            ->label('Название клуба')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city_ru')
                            ->label('Город')
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Английский')
                    ->schema([
                        TextInput::make('name_en')
                            ->label('Название клуба')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city_en')
                            ->label('Город')
                            ->required()
                            ->maxLength(255),
                    ]),
            ]);
    }
}
