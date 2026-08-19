<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            // Identity
            $table->uuid('id')->primary();
            $table->string('member_code', 20)->unique(); // BSA-00001
            $table->string('first_name', 80);
            $table->string('middle_name', 80)->nullable();
            $table->string('last_name', 80);
            $table->string('photo_url', 500)->nullable();
            $table->string('photo_public_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable(); // Gender enum
            $table->string('blood_group', 6)->nullable(); // BloodGroup enum

            // Contact
            $table->string('phone', 20)->index(); // primary identifier; uniqueness enforced in form validation withoutTrashed()
            $table->string('alt_phone', 20)->nullable();
            $table->string('email')->nullable();

            // Nepal address
            $table->string('province', 50)->nullable();
            $table->string('district', 50)->nullable();
            $table->string('municipality', 80)->nullable();
            $table->unsignedTinyInteger('ward_no')->nullable();
            $table->string('tole', 120)->nullable();

            // Profile
            $table->string('occupation', 80)->nullable();
            $table->string('institution', 120)->nullable();

            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relation', 40)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();

            // Guardian (minors)
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation', 40)->nullable();
            $table->string('guardian_phone', 20)->nullable();

            // Government ID & health
            $table->string('govt_id_type', 30)->nullable(); // GovtIdType enum
            $table->string('govt_id_number', 60)->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();

            // Acquisition
            $table->string('referral_source', 30)->nullable(); // ReferralSource enum
            $table->foreignUuid('referred_by_member_id')->nullable()->references('id')->on('members')->nullOnDelete();
            $table->boolean('marketing_consent')->default(false);

            // Lifecycle
            $table->string('status', 20)->default('active'); // MemberStatus enum
            $table->text('blacklist_reason')->nullable();
            $table->text('notes')->nullable();
            $table->date('joined_on');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes(); // financial history must survive member deletion
            $table->timestamps();

            $table->index('status');
            $table->index(['last_name', 'first_name']);
            $table->index('joined_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
