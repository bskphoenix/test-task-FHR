<?php

namespace App\Filament\Resources\PlayerSeasonMemberships\Pages;

use App\Filament\Resources\PlayerSeasonMemberships\PlayerSeasonMembershipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlayerSeasonMembership extends EditRecord
{
    protected static string $resource = PlayerSeasonMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
