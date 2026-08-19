<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('device_uid', 60)->unique(); // hardware-reported identifier
            $table->string('type', 20); // DeviceType enum
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('direction', 10)->default('entry'); // DeviceDirection enum
            $table->string('ip_address', 45)->nullable();
            $table->string('firmware', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_devices');
    }
};
