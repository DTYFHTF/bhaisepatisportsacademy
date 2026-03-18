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
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('color_name');
            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_at_price')->nullable();
            $table->string('category');
            $table->text('description');
            $table->text('fabric_story');
            $table->string('wardrobe_role')->nullable();
            $table->json('tags')->default('[]');
            $table->boolean('is_active')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });

        Schema::create('product_pairs', function (Blueprint $table) {
            $table->uuid('product_id');
            $table->uuid('paired_with_id');
            $table->primary(['product_id', 'paired_with_id']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('paired_with_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_pairs');
        Schema::dropIfExists('products');
    }
};
