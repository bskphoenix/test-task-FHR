<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ResolvesOwnerKey
{
    /** Возвращает ключ владельца сессии (пользователь или гость) */
    protected function resolveOwnerKey(Request $request): string
    {
        $userId = auth()->id();

        return $userId !== null ? 'user:' . $userId : 'session:' . $request->session()->getId();
    }
}
