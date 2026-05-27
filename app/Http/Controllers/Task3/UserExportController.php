<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task3;

use App\Http\Controllers\Concerns\ResolvesOwnerKey;
use App\Http\Controllers\Controller;
use App\Services\UserExport\UserCsvExportSessionStore;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class UserExportController extends Controller
{
    use ResolvesOwnerKey;

    /** Отдаёт CSV-файл завершённой выгрузки пользователей */
    public function download(
        string $exportId,
        Request $request,
        UserCsvExportSessionStore $sessionStore,
    ): BinaryFileResponse {
        $ownerKey = $this->resolveOwnerKey($request);
        $filePath = $sessionStore->getDownloadPath($exportId, $ownerKey);

        if ($filePath === null) {
            abort(404);
        }

        return response()->download(
            $filePath,
            'users-' . now()->format('Y-m-d-His') . '.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
