<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_slots', function (Blueprint $table) {
            $table->id();
            $table->string('day'); // Sunday, Monday, ...
            $table->string('time'); // "6:00 AM – 8:00 AM"
            $table->string('program_name');
            $table->string('court');
            $table->string('coach');
            $table->string('level'); // beginner | intermediate | advanced | all
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('day');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_slots');
    }
};
