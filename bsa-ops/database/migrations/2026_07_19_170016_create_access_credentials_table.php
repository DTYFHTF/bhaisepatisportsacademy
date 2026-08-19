<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // CredentialType enum
            $table->string('identifier_hash', 64)->unique(); // sha256(raw); raw identifier never stored
            $table->string('identifier_hint', 10)->nullable(); // last 4 chars, for humans
            $table->string('label')->nullable();
            $table->unsignedBigInteger('deposit_amount')->default(0); // paisa (card deposit)
            $table->timestamp('deposit_refunded_at')->nullable();
            $table->string('status', 20)->default('active'); // CredentialStatus enum
            $table->timestamp('issued_at');
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoke_reason')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_credentials');
    }
};
