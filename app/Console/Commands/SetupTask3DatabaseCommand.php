<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\ExportUsersSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupTask3DatabaseCommand extends Command
{
    protected $signature = 'task3:setup
                            {--count=500001 : Количество тестовых пользователей}
                            {--fresh : Пересоздать таблицы перед заполнением}';

    protected $description = 'Мигрирует отдельную БД задачи 3 и заполняет её тестовыми пользователями';

    /** Подготавливает отдельную БД для задачи 3 */
    public function handle(ExportUsersSeeder $seeder): int
    {
        $count = max(500_001, (int) $this->option('count'));

        if ($this->option('fresh')) {
            Artisan::call('migrate:fresh', [
                '--database' => 'task3_users',
                '--path' => 'database/migrations/task3',
                '--force' => true,
            ]);
            $this->output->write(Artisan::output());
        } else {
            Artisan::call('migrate', [
                '--database' => 'task3_users',
                '--path' => 'database/migrations/task3',
                '--force' => true,
            ]);
            $this->output->write(Artisan::output());
        }

        $this->components->info(sprintf('Заполнение %s пользователей...', number_format($count, 0, ',', ' ')));

        $startedAt = microtime(true);
        $seeder->run($count);
        $duration = microtime(true) - $startedAt;

        $this->components->info(sprintf(
            'Готово: %s пользователей за %s сек.',
            number_format($count, 0, ',', ' '),
            number_format($duration, 2, ',', ' '),
        ));

        return self::SUCCESS;
    }
}
