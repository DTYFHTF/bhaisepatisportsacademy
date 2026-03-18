<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('service_id')->constrained();
            $table->string('service_name'); // denormalised for history
            $table->integer('duration'); // minutes
            $table->integer('unit_price'); // paisa
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
