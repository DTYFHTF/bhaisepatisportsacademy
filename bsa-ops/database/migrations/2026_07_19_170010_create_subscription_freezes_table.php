<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Freeze pauses a subscription; on lift, ends_on is extended by the days
        // actually frozen, capped so cumulative frozen days <= plan freeze_allowance_days.
        Schema::create('subscription_freezes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_subscription_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedSmallInteger('days_count');
            $table->string('reason')->nullable();
            $table->timestamp('lifted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_freezes');
    }
};
