<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('looks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('look_hash', 8)->unique();
            $table->string('display_name')->nullable();
            $table->string('phone_hash')->nullable();
            $table->text('ai_explanation')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('phone_hash');
        });

        Schema::create('look_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('look_id');
            $table->uuid('product_id');
            $table->unsignedInteger('order')->default(0);

            $table->foreign('look_id')->references('id')->on('looks')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products');
            $table->index('look_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('look_items');
        Schema::dropIfExists('looks');
    }
};
