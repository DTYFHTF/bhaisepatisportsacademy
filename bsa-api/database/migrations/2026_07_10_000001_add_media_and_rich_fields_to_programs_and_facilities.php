<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Media (Cloudinary upload → derived delivery URL)
            $table->string('image_url', 500)->nullable()->after('features');
            $table->string('cloudinary_id')->nullable()->after('image_url');
            // Richer academy detail
            $table->string('coach_name')->nullable()->after('cloudinary_id');
            $table->string('highlight')->nullable()->after('coach_name'); // short hook shown on the card
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->string('cloudinary_id')->nullable()->after('image_url');
            $table->string('hours')->nullable()->after('cloudinary_id');    // e.g. "6:00 AM – 9:00 PM"
            $table->string('capacity')->nullable()->after('hours');         // e.g. "6 courts", "20+ people"
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'cloudinary_id', 'coach_name', 'highlight']);
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['cloudinary_id', 'hours', 'capacity']);
        });
    }
};
