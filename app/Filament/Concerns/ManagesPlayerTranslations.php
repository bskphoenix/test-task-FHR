<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\Player;

trait ManagesPlayerTranslations
{
    /** @var array<string, array{full_name: string}> */
    protected array $playerTranslationPayload = [];

    /** @param  array<string, mixed>  $data */
    protected function extractPlayerTranslations(array &$data): array
    {
        $this->playerTranslationPayload = [
            'ru' => ['full_name' => (string) ($data['full_name_ru'] ?? '')],
            'en' => ['full_name' => (string) ($data['full_name_en'] ?? '')],
        ];

        unset($data['full_name_ru'], $data['full_name_en']);

        return $data;
    }

    protected function syncPlayerTranslations(Player $player): void
    {
        foreach ($this->playerTranslationPayload as $locale => $fields) {
            $player->translations()->updateOrCreate(
                ['locale' => $locale],
                $fields,
            );
        }
    }

    /** @return array<string, mixed> */
    protected function mergePlayerTranslationsIntoForm(Player $player): array
    {
        $data = $player->attributesToArray();

        foreach ($player->translations as $translation) {
            $data['full_name_'.$translation->locale] = $translation->full_name;
        }

        return $data;
    }
}
