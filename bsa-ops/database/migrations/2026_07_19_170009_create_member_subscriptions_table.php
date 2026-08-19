<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('membership_plan_id')->constrained();
            $table->unsignedBigInteger('price'); // paisa snapshot at time of sale
            $table->unsignedBigInteger('admission_fee')->default(0); // paisa snapshot
            $table->foreignUuid('discount_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('discount_amount')->default(0); // paisa
            $table->date('starts_on');
            $table->date('ends_on')->nullable(); // packs: starts_on + validity_days
            $table->unsignedSmallInteger('sessions_total')->nullable();
            $table->unsignedSmallInteger('sessions_remaining')->nullable();
            $table->string('status', 20)->default('pending'); // SubscriptionStatus enum
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignUuid('renewed_from_id')->nullable()->references('id')->on('member_subscriptions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index('ends_on');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_subscriptions');
    }
};
