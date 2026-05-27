<?php

declare(strict_types=1);

namespace App\Filament\Resources\Clubs\Pages;

use App\Filament\Concerns\ManagesClubTranslations;
use App\Filament\Resources\Clubs\ClubResource;
use App\Models\Club;
use Filament\Resources\Pages\CreateRecord;

class CreateClub extends CreateRecord
{
    use ManagesClubTranslations;

    protected static string $resource = ClubResource::class;

    /** @param  array<string, mixed>  $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractClubTranslations($data);
    }

    protected function afterCreate(): void
    {
        /** @var Club $club */
        $club = $this->record;

        $this->syncClubTranslations($club);
    }
}
