<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only stock ledger — the source of truth for inventory.
        // products.stock_on_hand is a cache of SUM(quantity) per product.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained();
            $table->integer('quantity'); // signed: +in (purchase), -out (sale/consumption)
            $table->string('type', 20); // StockMovementType enum
            // Cost center for internal consumption (shuttlecocks -> BADMINTON)
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('unit_cost')->nullable(); // paisa, for consumption valuation
            $table->string('reference_type')->nullable(); // morph: Purchase, Invoice…
            $table->uuid('reference_id')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamp('created_at')->nullable(); // append-only: no updated_at

            $table->index(['product_id', 'occurred_at']);
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
