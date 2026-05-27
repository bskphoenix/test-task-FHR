<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportUser extends Model
{
    protected $connection = 'task3_users';

    protected $table = 'export_users';

    /** @var list<string> */
    protected $fillable = [
        'last_name',
        'first_name',
        'phone',
        'email',
    ];
}
