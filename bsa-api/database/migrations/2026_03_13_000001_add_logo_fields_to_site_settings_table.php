<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // Paths relative to storage/app/public - served via /storage/...
            $table->string('logo_path')->nullable()->after('store_tagline')
                ->comment('Uploaded logo. Served via Storage::url(). Null = use static fallback.');
            $table->string('icon_path')->nullable()->after('logo_path')
                ->comment('Uploaded small icon/favicon variant. Null = use static fallback.');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'icon_path']);
        });
    }
};
