<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Models\Club;
use App\Models\Player;

final class RosterLabels
{
    /** Возвращает локализованное название клуба */
    public static function clubName(Club $club, string $locale = 'ru'): string
    {
        return $club->translate($locale)?->name
            ?? $club->translate('ru')?->name
            ?? ('Клуб #'.$club->id);
    }

    /** Возвращает локализованное ФИО игрока */
    public static function playerName(Player $player, string $locale = 'ru'): string
    {
        return $player->translate($locale)?->full_name
            ?? $player->translate('ru')?->full_name
            ?? ('Игрок #'.$player->id);
    }
}
