<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'task3_users';

    /** Создаёт таблицу пользователей для выгрузки в отдельной БД */
    public function up(): void
    {
        Schema::connection('task3_users')->create('export_users', function (Blueprint $table): void {
            $table->id();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('phone', 32);
            $table->string('email');
            $table->timestamps();
        });
    }

    /** Удаляет таблицу пользователей для выгрузки */
    public function down(): void
    {
        Schema::connection('task3_users')->dropIfExists('export_users');
    }
};
