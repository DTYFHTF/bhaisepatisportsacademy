<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id')->unique();
            $table->string('phone_hash');
            $table->string('customer_name');
            $table->string('address');
            $table->string('city');
            $table->text('delivery_note')->nullable();
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('delivery_fee');
            $table->unsignedInteger('total');
            $table->string('payment_method');
            $table->string('payment_ref')->nullable();
            $table->string('status')->default('PENDING_PAYMENT');
            $table->boolean('exchange_requested')->default(false);
            $table->timestamps();

            $table->index('phone_hash');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
