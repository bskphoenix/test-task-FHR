<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'weight_kg',
        'height_cm',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'height_cm' => 'integer',
        ];
    }

    /** Переводы ФИО игрока */
    public function translations(): HasMany
    {
        return $this->hasMany(PlayerTranslation::class);
    }

    /** Членства игрока по сезонам */
    public function memberships(): HasMany
    {
        return $this->hasMany(PlayerSeasonMembership::class);
    }

    /** Возвращает перевод ФИО для указанной локали */
    public function translate(string $locale): ?PlayerTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }
}
