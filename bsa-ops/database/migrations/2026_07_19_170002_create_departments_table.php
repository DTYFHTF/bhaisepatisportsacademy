<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique(); // GYM, POOL, SAUNA, BADMINTON, FUTSAL
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('cost_center_code', 20)->nullable();
            $table->unsignedBigInteger('monthly_budget')->nullable(); // paisa
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->string('color', 20)->nullable(); // Filament badge color hint
            // false = pure cost/revenue center (Kitchen, Pro Shop): no door
            // gating, hidden from the kiosk and eligibility checks.
            $table->boolean('is_access_controlled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
