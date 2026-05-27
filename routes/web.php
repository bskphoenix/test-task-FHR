<?php

declare(strict_types=1);

use App\Http\Controllers\Task1\BubbleSortDownloadController;
use App\Http\Controllers\Task3\UserExportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/admin/task-1/download/original', [BubbleSortDownloadController::class, 'downloadOriginal'])
        ->name('task1.download.original');

    Route::get('/admin/task-1/download/sorted', [BubbleSortDownloadController::class, 'downloadSorted'])
        ->name('task1.download.sorted');

    Route::get('/admin/task-3/download/{exportId}', [UserExportController::class, 'download'])
        ->name('task3.download');
});
