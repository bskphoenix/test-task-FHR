<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\Club;

trait ManagesClubTranslations
{
    /** @var array<string, array{name: string, city: string}> */
    protected array $clubTranslationPayload = [];

    /** @param  array<string, mixed>  $data */
    protected function extractClubTranslations(array &$data): array
    {
        $this->clubTranslationPayload = [
            'ru' => [
                'name' => (string) ($data['name_ru'] ?? ''),
                'city' => (string) ($data['city_ru'] ?? ''),
            ],
            'en' => [
                'name' => (string) ($data['name_en'] ?? ''),
                'city' => (string) ($data['city_en'] ?? ''),
            ],
        ];

        unset($data['name_ru'], $data['city_ru'], $data['name_en'], $data['city_en']);

        return $data;
    }

    protected function syncClubTranslations(Club $club): void
    {
        foreach ($this->clubTranslationPayload as $locale => $fields) {
            $club->translations()->updateOrCreate(
                ['locale' => $locale],
                $fields,
            );
        }
    }

    /** @return array<string, mixed> */
    protected function mergeClubTranslationsIntoForm(Club $club): array
    {
        $data = $club->attributesToArray();

        foreach ($club->translations as $translation) {
            $data['name_'.$translation->locale] = $translation->name;
            $data['city_'.$translation->locale] = $translation->city;
        }

        return $data;
    }
}
