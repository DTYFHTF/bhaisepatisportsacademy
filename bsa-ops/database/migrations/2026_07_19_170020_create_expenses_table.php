<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('voucher_number', 30)->unique(); // VCH-2082-83-0001
            $table->foreignUuid('expense_category_id')->constrained();
            // null department = shared overhead, allocated pro-rata in the P&L report
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->unsignedBigInteger('amount'); // paisa
            $table->date('expense_date');
            $table->string('payment_method', 20); // PaymentMethod enum
            $table->string('vendor_name')->nullable();
            $table->string('reference_no')->nullable(); // bill / PAN bill number
            $table->string('receipt_url', 500)->nullable();
            $table->string('status', 20)->default('recorded'); // ExpenseStatus enum
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('expense_date');
            $table->index(['department_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
