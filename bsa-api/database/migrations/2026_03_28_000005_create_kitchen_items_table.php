<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->unsignedInteger('price'); // paisa
            $table->string('category'); // pre-workout | post-workout | snacks | drinks
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_items');
    }
};
