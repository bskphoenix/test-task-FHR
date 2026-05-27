<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Club;
use App\Models\ClubTranslation;
use App\Models\Locale;
use App\Models\Player;
use App\Models\PlayerSeasonMembership;
use App\Models\PlayerTranslation;
use App\Models\Season;
use Illuminate\Database\Seeder;

class RosterSeeder extends Seeder
{
    /** Заполняет справочники и демо-данные составов */
    public function run(): void
    {
        $this->seedLocales();
        $seasons = $this->seedSeasons();
        $clubs = $this->seedClubs();
        $players = $this->seedPlayers();
        $this->seedMemberships($seasons, $clubs, $players);
    }

    private function seedLocales(): void
    {
        Locale::query()->upsert([
            [
                'code' => 'ru',
                'name' => 'Русский',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], uniqueBy: ['code'], update: ['name', 'is_default', 'is_active', 'sort_order', 'updated_at']);
    }

    /**
     * @return array{previous: Season, current: Season}
     */
    private function seedSeasons(): array
    {
        $previous = Season::query()->updateOrCreate(
            ['slug' => '2024-2025'],
            [
                'starts_at' => '2024-09-01',
                'ends_at' => '2025-06-30',
                'is_active' => false,
            ],
        );

        $current = Season::query()->updateOrCreate(
            ['slug' => '2025-2026'],
            [
                'starts_at' => '2025-09-01',
                'ends_at' => '2026-06-30',
                'is_active' => true,
            ],
        );

        return [
            'previous' => $previous,
            'current' => $current,
        ];
    }

    /**
     * @return array<string, Club>
     */
    private function seedClubs(): array
    {
        $definitions = [
            'spartak' => [
                'id' => 1,
                'ru' => ['name' => 'Спартак', 'city' => 'Москва'],
                'en' => ['name' => 'Spartak', 'city' => 'Moscow'],
            ],
            'cska' => [
                'id' => 2,
                'ru' => ['name' => 'ЦСКА', 'city' => 'Москва'],
                'en' => ['name' => 'CSKA', 'city' => 'Moscow'],
            ],
            'ska' => [
                'id' => 3,
                'ru' => ['name' => 'СКА', 'city' => 'Санкт-Петербург'],
                'en' => ['name' => 'SKA', 'city' => 'Saint Petersburg'],
            ],
        ];

        $clubs = [];

        foreach ($definitions as $key => $translations) {
            $club = Club::query()->updateOrCreate(
                ['id' => $translations['id']],
                [],
            );

            foreach ($translations as $locale => $data) {
                if ($locale === 'id') {
                    continue;
                }

                ClubTranslation::query()->updateOrCreate(
                    [
                        'club_id' => $club->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $data['name'],
                        'city' => $data['city'],
                    ],
                );
            }

            $clubs[$key] = $club;
        }

        return $clubs;
    }

    /**
     * @return array<string, Player>
     */
    private function seedPlayers(): array
    {
        $definitions = [
            'ivanov' => [
                'weight_kg' => 87.00,
                'height_cm' => 182,
                'ru' => 'Иванов Иван Иванович',
                'en' => 'Ivan Ivanov',
            ],
            'petrov' => [
                'weight_kg' => 90.00,
                'height_cm' => 188,
                'ru' => 'Петров Пётр Петрович',
                'en' => 'Pyotr Petrov',
            ],
            'sidorov' => [
                'weight_kg' => 78.50,
                'height_cm' => 175,
                'ru' => 'Сидоров Сидор Сидорович',
                'en' => 'Sidor Sidorov',
            ],
            'kozlov' => [
                'weight_kg' => 92.00,
                'height_cm' => 190,
                'ru' => 'Козлов Алексей Алексеевич',
                'en' => 'Alexey Kozlov',
            ],
            'smirnov' => [
                'weight_kg' => 84.00,
                'height_cm' => 180,
                'ru' => 'Смирнов Дмитрий Дмитриевич',
                'en' => 'Dmitry Smirnov',
            ],
        ];

        $players = [];

        foreach ($definitions as $key => $data) {
            $player = Player::query()->updateOrCreate(
                ['id' => $this->resolvePlayerId($key)],
                [
                    'weight_kg' => $data['weight_kg'],
                    'height_cm' => $data['height_cm'],
                ],
            );

            foreach (['ru', 'en'] as $locale) {
                PlayerTranslation::query()->updateOrCreate(
                    [
                        'player_id' => $player->id,
                        'locale' => $locale,
                    ],
                    [
                        'full_name' => $data[$locale],
                    ],
                );
            }

            $players[$key] = $player;
        }

        return $players;
    }

    /**
     * @param  array{previous: Season, current: Season}  $seasons
     * @param  array<string, Club>  $clubs
     * @param  array<string, Player>  $players
     */
    private function seedMemberships(array $seasons, array $clubs, array $players): void
    {
        PlayerSeasonMembership::query()->delete();

        $rows = [
            // Сезон 2024/2025
            [
                'season_id' => $seasons['previous']->id,
                'player_id' => $players['ivanov']->id,
                'club_id' => $clubs['spartak']->id,
                'jersey_number' => 10,
                'joined_at' => '2024-09-01',
                'left_at' => null,
            ],
            [
                'season_id' => $seasons['previous']->id,
                'player_id' => $players['petrov']->id,
                'club_id' => $clubs['spartak']->id,
                'jersey_number' => 11,
                'joined_at' => '2024-09-01',
                'left_at' => null,
            ],
            [
                'season_id' => $seasons['previous']->id,
                'player_id' => $players['sidorov']->id,
                'club_id' => $clubs['cska']->id,
                'jersey_number' => 9,
                'joined_at' => '2024-09-01',
                'left_at' => null,
            ],
            [
                'season_id' => $seasons['previous']->id,
                'player_id' => $players['kozlov']->id,
                'club_id' => $clubs['ska']->id,
                'jersey_number' => 17,
                'joined_at' => '2024-09-01',
                'left_at' => null,
            ],
            [
                'season_id' => $seasons['previous']->id,
                'player_id' => $players['smirnov']->id,
                'club_id' => $clubs['cska']->id,
                'jersey_number' => 7,
                'joined_at' => '2024-09-01',
                'left_at' => null,
            ],

            // Сезон 2025/2026
            [
                'season_id' => $seasons['current']->id,
                'player_id' => $players['ivanov']->id,
                'club_id' => $clubs['spartak']->id,
                'jersey_number' => 10,
                'joined_at' => '2025-09-01',
                'left_at' => '2026-01-15',
            ],
            [
                'season_id' => $seasons['current']->id,
                'player_id' => $players['ivanov']->id,
                'club_id' => $clubs['cska']->id,
                'jersey_number' => 7,
                'joined_at' => '2026-01-16',
                'left_at' => null,
            ],
            [
                'season_id' => $seasons['current']->id,
                'player_id' => $players['petrov']->id,
                'club_id' => $clubs['spartak']->id,
                'jersey_number' => 11,
                'joined_at' => '2025-09-01',
                'left_at' => null,
            ],
            [
                'season_id' => $seasons['current']->id,
                'player_id' => $players['sidorov']->id,
                'club_id' => $clubs['cska']->id,
                'jersey_number' => 9,
                'joined_at' => '2025-09-01',
                'left_at' => null,
            ],
            [
                'season_id' => $seasons['current']->id,
                'player_id' => $players['kozlov']->id,
                'club_id' => $clubs['ska']->id,
                'jersey_number' => 17,
                'joined_at' => '2025-09-01',
                'left_at' => null,
            ],
            [
                'season_id' => $seasons['current']->id,
                'player_id' => $players['smirnov']->id,
                'club_id' => $clubs['cska']->id,
                'jersey_number' => 7,
                'joined_at' => '2025-09-01',
                'left_at' => '2025-11-01',
            ],
            [
                'season_id' => $seasons['current']->id,
                'player_id' => $players['smirnov']->id,
                'club_id' => $clubs['ska']->id,
                'jersey_number' => 22,
                'joined_at' => '2025-11-02',
                'left_at' => null,
            ],
        ];

        foreach ($rows as $row) {
            PlayerSeasonMembership::query()->create($row);
        }
    }

    /** Возвращает стабильный id игрока для повторного сидирования */
    private function resolvePlayerId(string $key): int
    {
        return match ($key) {
            'ivanov' => 1,
            'petrov' => 2,
            'sidorov' => 3,
            'kozlov' => 4,
            'smirnov' => 5,
        };
    }
}
