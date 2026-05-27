<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExportUsersSeeder extends Seeder
{
    public const int DEFAULT_USER_COUNT = 500_001;

    private const int BATCH_SIZE = 2_000;

    /** Заполняет тестовую БД пользователями для выгрузки */
    public function run(int $count = self::DEFAULT_USER_COUNT): void
    {
        $connection = DB::connection('task3_users');

        $connection->table('export_users')->truncate();

        $now = now()->toDateTimeString();

        for ($offset = 1; $offset <= $count; $offset += self::BATCH_SIZE) {
            $batch = [];
            $limit = min($offset + self::BATCH_SIZE - 1, $count);

            for ($index = $offset; $index <= $limit; $index++) {
                $batch[] = [
                    'last_name' => 'Фамилия' . $index,
                    'first_name' => 'Имя' . $index,
                    'phone' => '+7' . str_pad((string) $index, 10, '0', STR_PAD_LEFT),
                    'email' => 'user' . $index . '@example.com',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $connection->table('export_users')->insert($batch);
        }
    }
}
