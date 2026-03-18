<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('delivery_note');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('formatted_address')->nullable()->after('longitude');
            $table->string('nearest_landmark')->nullable()->after('formatted_address');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'formatted_address', 'nearest_landmark']);
        });
    }
};
