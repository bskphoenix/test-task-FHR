<?php

declare(strict_types=1);

namespace App\Filament\Resources\Players\Pages;

use App\Filament\Concerns\ManagesPlayerTranslations;
use App\Filament\Resources\Players\PlayerResource;
use App\Models\Player;
use Filament\Resources\Pages\CreateRecord;

class CreatePlayer extends CreateRecord
{
    use ManagesPlayerTranslations;

    protected static string $resource = PlayerResource::class;

    /** @param  array<string, mixed>  $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractPlayerTranslations($data);
    }

    protected function afterCreate(): void
    {
        /** @var Player $player */
        $player = $this->record;

        $this->syncPlayerTranslations($player);
    }
}
