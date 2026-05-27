<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Создаёт таблицы для клубов, игроков и сезонных составов */
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('club_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('city');
            $table->timestamps();

            $table->unique(['club_id', 'locale']);
            $table->index(['locale', 'name']);
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->timestamps();
        });

        Schema::create('player_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('full_name');
            $table->timestamps();

            $table->unique(['player_id', 'locale']);
            $table->index(['locale', 'full_name']);
        });

        Schema::create('player_season_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('jersey_number');
            $table->date('joined_at');
            $table->date('left_at')->nullable();
            $table->timestamps();

            $table->index(['season_id', 'club_id', 'left_at']);
            $table->index(['season_id', 'player_id', 'left_at']);
        });
    }

    /** Удаляет таблицы составов */
    public function down(): void
    {
        Schema::dropIfExists('player_season_memberships');
        Schema::dropIfExists('player_translations');
        Schema::dropIfExists('players');
        Schema::dropIfExists('club_translations');
        Schema::dropIfExists('clubs');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('locales');
    }
};
