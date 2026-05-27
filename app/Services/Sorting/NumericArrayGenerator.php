<?php

declare(strict_types=1);

namespace App\Services\Sorting;

final class NumericArrayGenerator
{
    /**
     * Генерирует массив случайных целых чисел заданного размера.
     *
     * @return list<int>
     */
    public function generate(int $count, ?int $seed = null): array
    {
        if ($seed !== null) {
            mt_srand($seed);
        }

        $data = [];

        for ($index = 0; $index < $count; $index++) {
            $data[] = random_int(-1_000_000, 1_000_000);
        }

        return $data;
    }
}
