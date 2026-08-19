<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only raw audit log of every hardware access attempt.
        Schema::create('access_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('access_device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_uid', 60);
            $table->string('credential_hint', 64)->nullable(); // hash presented by the device
            $table->foreignUuid('access_credential_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('decision', 10); // AccessDecision enum
            $table->string('deny_reason', 40)->nullable(); // DenialReason enum
            $table->timestamp('occurred_at')->useCurrent();
            $table->json('raw_payload')->nullable();
            $table->timestamp('created_at')->nullable(); // append-only: no updated_at

            $table->index('occurred_at');
            $table->index(['access_device_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_events');
    }
};
