<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A plan may cover several departments (e.g. All-Access).
        Schema::create('department_membership_plan', function (Blueprint $table) {
            $table->foreignUuid('membership_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained()->cascadeOnDelete();
            $table->primary(['membership_plan_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_membership_plan');
    }
};
