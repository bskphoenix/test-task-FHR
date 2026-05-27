<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubTranslation extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'club_id',
        'locale',
        'name',
        'city',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
