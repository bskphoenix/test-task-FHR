<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    /** @var list<string> */
    protected $fillable = [];

    /** Переводы названия и города клуба */
    public function translations(): HasMany
    {
        return $this->hasMany(ClubTranslation::class);
    }

    /** Членства игроков в клубе */
    public function memberships(): HasMany
    {
        return $this->hasMany(PlayerSeasonMembership::class);
    }

    /** Возвращает перевод клуба для указанной локали */
    public function translate(string $locale): ?ClubTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }
}
