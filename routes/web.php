<?php

declare(strict_types=1);

use App\Services\Sorting\BubbleSortCancellation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware(['web', 'auth'])->post('/admin/task-1/cancel', function (Request $request, BubbleSortCancellation $cancellation) {
    $userId = auth()->id();
    $ownerKey = $userId !== null ? 'user:' . $userId : 'session:' . $request->session()->getId();
    $runId = $request->string('run_id')->toString();

    if ($runId === '') {
        $runId = $cancellation->getActiveRunId($ownerKey) ?? '';
    }

    if ($runId === '') {
        return response()->json(['message' => 'Нет активной сортировки'], 404);
    }

    $cancellation->requestStop($runId);

    return response()->json(['ok' => true]);
})->name('task1.cancel');
