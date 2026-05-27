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
    public function generate(int $count): array
    {
        $data = array_fill(0, $count, 0);

        for ($index = 0; $index < $count; $index++) {
            $data[$index] = random_int(-1_000_000, 1_000_000);
        }

        return $data;
    }
}
