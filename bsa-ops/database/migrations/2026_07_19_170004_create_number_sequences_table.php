<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Race-safe number allocation: rows locked FOR UPDATE inside a transaction.
        // Keys: member_code, invoice:{fy}, receipt:{fy}, voucher:{fy}
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->unsignedBigInteger('next_value')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
    }
};
