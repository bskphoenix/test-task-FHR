<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task1;

use App\Http\Controllers\Controller;
use App\Services\Sorting\BubbleSortResultStore;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class BubbleSortDownloadController extends Controller
{
    /** Отдаёт исходный массив в CSV */
    public function downloadOriginal(BubbleSortResultStore $resultStore): BinaryFileResponse
    {
        return $this->downloadCsv(
            $resultStore->originalCsvPath(),
            'original.csv',
        );
    }

    /** Отдаёт отсортированный массив в CSV */
    public function downloadSorted(BubbleSortResultStore $resultStore): BinaryFileResponse
    {
        return $this->downloadCsv(
            $resultStore->sortedCsvPath(),
            'sorted.csv',
        );
    }

    private function downloadCsv(string $path, string $downloadName): BinaryFileResponse
    {
        if (! is_file($path)) {
            abort(404);
        }

        return response()->download(
            $path,
            $downloadName,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
