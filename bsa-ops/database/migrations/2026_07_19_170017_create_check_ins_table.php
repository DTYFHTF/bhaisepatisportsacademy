<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained();
            $table->foreignUuid('department_id')->constrained();
            // Which subscription granted entry / had a session consumed
            $table->foreignUuid('member_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('checked_in_at')->useCurrent();
            $table->string('source', 20); // CheckInSource enum
            $table->boolean('was_allowed')->default(true);
            $table->string('denial_reason', 40)->nullable(); // DenialReason enum
            $table->boolean('session_consumed')->default(false);
            $table->foreignUuid('access_device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['member_id', 'checked_in_at']);
            $table->index(['department_id', 'checked_in_at']);
            $table->index('checked_in_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
