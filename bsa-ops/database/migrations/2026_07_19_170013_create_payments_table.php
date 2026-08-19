<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('receipt_number', 30)->unique(); // RCP-2082-83-0001
            $table->foreignUuid('invoice_id')->constrained();
            // Denormalized for member ledger queries; nullable for walk-in POS sales.
            $table->foreignUuid('member_id')->nullable()->constrained();
            $table->unsignedBigInteger('amount'); // paisa
            $table->string('method', 20); // PaymentMethod enum
            $table->string('status', 30)->default('completed'); // PaymentStatus enum
            $table->string('gateway_txn_id')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->string('cheque_number', 30)->nullable();
            $table->string('cheque_bank', 80)->nullable();
            $table->date('cheque_date')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'received_at']);
            $table->index('method');
            $table->index('status');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
