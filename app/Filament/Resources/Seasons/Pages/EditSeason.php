<?php

declare(strict_types=1);

namespace App\Filament\Resources\Seasons\Pages;

use App\Filament\Concerns\ManagesActiveSeason;
use App\Filament\Resources\Seasons\SeasonResource;
use App\Models\Season;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSeason extends EditRecord
{
    use ManagesActiveSeason;

    protected static string $resource = SeasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Season $season */
        $season = $this->record;

        $this->deactivateOtherSeasons($season);
    }
}
