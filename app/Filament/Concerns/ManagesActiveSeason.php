<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\Season;

trait ManagesActiveSeason
{
    /** Оставляет активным только один сезон */
    protected function deactivateOtherSeasons(Season $season): void
    {
        if (! $season->is_active) {
            return;
        }

        Season::query()
            ->whereKeyNot($season->id)
            ->update(['is_active' => false]);
    }
}
