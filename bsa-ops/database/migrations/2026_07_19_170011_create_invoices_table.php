<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number', 30)->unique(); // INV-2082-83-0001 (fiscal-year sequence)
            // Nullable: POS walk-in sales have no member.
            $table->foreignUuid('member_id')->nullable()->constrained();
            $table->string('source', 20)->default('membership'); // InvoiceSource enum
            $table->foreignUuid('member_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->unsignedBigInteger('subtotal'); // paisa, pre-discount pre-tax
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('taxable_amount')->default(0);
            $table->unsignedBigInteger('tax_total')->default(0);
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('paid_total')->default(0); // completed payments only
            $table->unsignedBigInteger('balance');
            $table->string('status', 20)->default('issued'); // InvoiceStatus enum
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index('source');
            $table->index('status');
            $table->index('due_date');
            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
