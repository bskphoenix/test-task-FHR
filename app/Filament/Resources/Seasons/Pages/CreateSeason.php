<?php

declare(strict_types=1);

namespace App\Filament\Resources\Seasons\Pages;

use App\Filament\Concerns\ManagesActiveSeason;
use App\Filament\Resources\Seasons\SeasonResource;
use App\Models\Season;
use Filament\Resources\Pages\CreateRecord;

class CreateSeason extends CreateRecord
{
    use ManagesActiveSeason;

    protected static string $resource = SeasonResource::class;

    protected function afterCreate(): void
    {
        /** @var Season $season */
        $season = $this->record;

        $this->deactivateOtherSeasons($season);
    }
}
