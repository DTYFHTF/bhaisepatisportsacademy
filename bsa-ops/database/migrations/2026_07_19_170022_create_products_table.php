<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku', 30)->unique(); // SHUT-YON-01
            $table->string('name');
            $table->string('category', 20); // ProductCategory: shop | kitchen | consumable
            // Cost/revenue attribution: shuttlecock -> BADMINTON, momo -> KITCHEN
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit', 20)->default('piece'); // piece / tube / plate / cup / kg / bucket
            $table->unsignedBigInteger('cost_price')->default(0); // paisa
            $table->unsignedBigInteger('price'); // paisa, walk-in price
            $table->unsignedBigInteger('member_price')->nullable(); // paisa, Club member price
            $table->boolean('is_taxable')->default(true);
            $table->boolean('price_includes_tax')->default(true);
            // Cooked kitchen dishes are made to order: no stock tracking.
            $table->boolean('track_stock')->default(true);
            // Cache of the stock_movements ledger sum; maintained transactionally.
            $table->integer('stock_on_hand')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('image_url', 500)->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('department_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
