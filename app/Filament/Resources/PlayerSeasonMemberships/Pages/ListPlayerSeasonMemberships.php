<?php

namespace App\Filament\Resources\PlayerSeasonMemberships\Pages;

use App\Filament\Resources\PlayerSeasonMemberships\PlayerSeasonMembershipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlayerSeasonMemberships extends ListRecords
{
    protected static string $resource = PlayerSeasonMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
