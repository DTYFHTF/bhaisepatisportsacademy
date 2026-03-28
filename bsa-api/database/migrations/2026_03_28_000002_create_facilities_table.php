<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('category'); // BADMINTON | GYM | SAUNA
            $table->string('icon')->default('🏸');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('features')->default('[]');
            $table->timestamps();
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
