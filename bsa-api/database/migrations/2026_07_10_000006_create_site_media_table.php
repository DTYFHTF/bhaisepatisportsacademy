<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();       // e.g. home_hero_poster
            $table->string('page_group');           // Home, Programs, Facilities, Kitchen, About
            $table->string('label');                // sub-heading shown in admin, e.g. "Hero Background — top of homepage"
            $table->string('cloudinary_id')->nullable();
            $table->string('url', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('page_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_media');
    }
};
