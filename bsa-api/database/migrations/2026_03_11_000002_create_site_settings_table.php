<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            // Brand
            $table->string('store_name')->default('Bhaisepati Sports Academy');
            $table->string('store_tagline')->default('Premium waxing studio in Kathmandu, Nepal.');

            // Contact
            $table->string('contact_email')->default('hello@bsa.example.com');
            $table->string('contact_phone')->default('9821357118');
            $table->string('contact_address')->default('Chakrapath, Kathmandu, Nepal');

            // Store location (used for delivery map)
            $table->decimal('store_lat', 10, 7)->default(27.7172);
            $table->decimal('store_lng', 10, 7)->default(85.3240);

            // Social links
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('whatsapp_number')->nullable()->comment('10-digit Nepal number, no country code');

            // Content snippets
            $table->string('delivery_tagline')->default('Free delivery on product orders over NPR 5,000.');
            $table->string('return_tagline')->default('Satisfaction guaranteed on every visit.');

            $table->timestamps();
        });

        // Seed the single-row default
        DB::table('site_settings')->insert([
            'store_name'       => 'Bhaisepati Sports Academy',
            'store_tagline'    => 'Premium waxing studio in Kathmandu, Nepal.',
            'contact_email'    => 'hello@bsa.example.com',
            'contact_phone'    => '9821357118',
            'contact_address'  => 'Chakrapath, Kathmandu, Nepal',
            'store_lat'        => 27.7172,
            'store_lng'        => 85.3240,
            'instagram_url'    => null,
            'facebook_url'     => null,
            'whatsapp_number'  => null,
            'delivery_tagline' => 'Free delivery on product orders over NPR 5,000.',
            'return_tagline'   => 'Satisfaction guaranteed on every visit.',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
