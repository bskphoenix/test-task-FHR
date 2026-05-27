<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clubs\Pages;

use App\Filament\Concerns\ManagesClubTranslations;
use App\Filament\Resources\Clubs\ClubResource;
use App\Models\Club;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClub extends EditRecord
{
    use ManagesClubTranslations;

    protected static string $resource = ClubResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /** @return array<string, mixed> */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Club $record */
        $record = $this->record;

        return $this->mergeClubTranslationsIntoForm($record);
    }

    /** @param  array<string, mixed>  $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->extractClubTranslations($data);
    }

    protected function afterSave(): void
    {
        /** @var Club $club */
        $club = $this->record;

        $this->syncClubTranslations($club);
    }
}
