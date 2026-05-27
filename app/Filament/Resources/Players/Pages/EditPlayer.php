<?php

declare(strict_types=1);

namespace App\Filament\Resources\Players\Pages;

use App\Filament\Concerns\ManagesPlayerTranslations;
use App\Filament\Resources\Players\PlayerResource;
use App\Models\Player;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlayer extends EditRecord
{
    use ManagesPlayerTranslations;

    protected static string $resource = PlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /** @return array<string, mixed> */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Player $record */
        $record = $this->record;

        return $this->mergePlayerTranslationsIntoForm($record);
    }

    /** @param  array<string, mixed>  $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->extractPlayerTranslations($data);
    }

    protected function afterSave(): void
    {
        /** @var Player $player */
        $player = $this->record;

        $this->syncPlayerTranslations($player);
    }
}
