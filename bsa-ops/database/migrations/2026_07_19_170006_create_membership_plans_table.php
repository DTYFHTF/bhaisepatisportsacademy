<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique(); // GYM-1M, POOL-PACK10
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('plan_type', 20); // PlanType: time_based | session_pack
            $table->string('interval_unit', 10)->nullable(); // IntervalUnit: days | months (time_based)
            $table->unsignedSmallInteger('interval_count')->nullable();
            $table->unsignedSmallInteger('session_count')->nullable(); // session_pack
            $table->unsignedSmallInteger('validity_days')->nullable(); // pack expiry window
            $table->unsignedBigInteger('price'); // paisa
            $table->unsignedBigInteger('admission_fee')->default(0); // paisa, first subscription only
            $table->boolean('is_taxable')->default(true);
            $table->boolean('price_includes_tax')->default(true);
            $table->unsignedSmallInteger('freeze_allowance_days')->default(0);
            $table->unsignedTinyInteger('guest_passes')->default(0);
            $table->boolean('is_off_peak')->default(false);
            $table->time('off_peak_start')->nullable();
            $table->time('off_peak_end')->nullable();
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('plan_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
