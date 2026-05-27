<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'slug',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /** Членства игроков в сезоне */
    public function memberships(): HasMany
    {
        return $this->hasMany(PlayerSeasonMembership::class);
    }
}
