<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restock_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone_hash');
            $table->uuid('product_id');
            $table->uuid('variant_id')->nullable();
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['phone_hash', 'variant_id']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete();
            $table->index('product_id');
            $table->index('notified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restock_alerts');
    }
};
