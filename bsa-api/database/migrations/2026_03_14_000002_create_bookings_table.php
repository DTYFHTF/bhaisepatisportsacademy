<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ref')->unique(); // PP-YYMM-XXXX
            $table->string('customer_name');
            $table->string('customer_phone'); // plain for now; HMAC in production
            $table->string('customer_email')->nullable();
            $table->date('scheduled_date');
            $table->string('scheduled_time'); // e.g. "14:30"
            $table->integer('total_duration'); // minutes
            $table->integer('total'); // paisa
            $table->string('status')->default('PENDING'); // BookingStatus enum
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
