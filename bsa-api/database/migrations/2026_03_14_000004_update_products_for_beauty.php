<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('color_name', 'tagline');
            $table->renameColumn('fabric_story', 'ingredients');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->text('how_to_use')->nullable()->after('ingredients');
            $table->text('suitable_for')->nullable()->after('how_to_use');
            $table->dropColumn('wardrobe_role');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->renameColumn('size', 'label');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('price_override')->nullable()->after('label');
        });

        // Drop the old unique index and create new one
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'size']);
            $table->unique(['product_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'label']);
            $table->unique(['product_id', 'size']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('price_override');
            $table->renameColumn('label', 'size');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['how_to_use', 'suitable_for']);
            $table->string('wardrobe_role')->nullable();
            $table->renameColumn('ingredients', 'fabric_story');
            $table->renameColumn('tagline', 'color_name');
        });
    }
};
