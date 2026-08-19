<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            // Morph to what was sold: membership plan, credential deposit, ad-hoc line
            $table->string('itemable_type')->nullable();
            $table->uuid('itemable_id')->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price'); // paisa
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // 13.00
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('line_total');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['itemable_type', 'itemable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
