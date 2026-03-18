<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier')->nullable()->after('status');         // e.g. PATHAO, NCM
            $table->string('courier_tracking_id')->nullable()->after('courier');
            $table->string('courier_tracking_url')->nullable()->after('courier_tracking_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['courier', 'courier_tracking_id', 'courier_tracking_url']);
        });
    }
};
