<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Type: 'court' for court bookings, 'trial' for trial sessions
            $table->string('type')->default('court')->after('ref');
            // Trial-specific fields (optional, only populated for trial bookings)
            $table->string('experience_level')->nullable()->after('notes');
            $table->unsignedSmallInteger('age')->nullable()->after('experience_level');
            $table->string('goals')->nullable()->after('age');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['type', 'experience_level', 'age', 'goals']);
        });
    }
};
