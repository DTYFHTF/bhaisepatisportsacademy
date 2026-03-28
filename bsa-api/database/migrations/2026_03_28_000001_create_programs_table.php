<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('category'); // BADMINTON | FITNESS | RECOVERY
            $table->string('level');    // beginner | intermediate | advanced | all
            $table->string('age_group');
            $table->string('duration'); // e.g. "1 month"
            $table->unsignedTinyInteger('sessions_per_week');
            $table->unsignedInteger('price'); // paisa
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('features')->default('[]');
            $table->timestamps();
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
