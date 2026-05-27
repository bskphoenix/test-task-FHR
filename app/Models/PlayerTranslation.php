<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerTranslation extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'player_id',
        'locale',
        'full_name',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
