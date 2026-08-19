<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('voucher_number', 30)->unique(); // PUR-2082-83-0001
            $table->foreignUuid('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->date('purchase_date');
            $table->string('reference_no')->nullable(); // supplier bill / PAN bill no
            $table->unsignedBigInteger('total'); // paisa
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('purchase_date');
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained();
            $table->integer('quantity');
            $table->unsignedBigInteger('unit_cost'); // paisa
            $table->unsignedBigInteger('line_total'); // paisa
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
