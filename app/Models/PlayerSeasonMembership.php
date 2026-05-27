<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSeasonMembership extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'season_id',
        'player_id',
        'club_id',
        'jersey_number',
        'joined_at',
        'left_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jersey_number' => 'integer',
            'joined_at' => 'date',
            'left_at' => 'date',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /** Проверяет, является ли членство активным */
    public function isActive(): bool
    {
        return $this->left_at === null;
    }
}
