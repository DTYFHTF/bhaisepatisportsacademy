<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('role');                       // e.g. "Head Badminton Coach"
            $table->text('bio')->nullable();
            $table->string('credentials')->nullable();    // e.g. "BWF Level 2 Certified"
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->json('specialties')->default('[]');    // e.g. ["Singles", "Footwork"]
            $table->string('image_url', 500)->nullable();
            $table->string('cloudinary_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaches');
    }
};
