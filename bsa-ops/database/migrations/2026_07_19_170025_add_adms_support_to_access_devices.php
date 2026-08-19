<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_devices', function (Blueprint $table) {
            // How this device talks to us. `native` = our JSON verify API;
            // `zkteco_adms` = ZKTeco's PUSH/ADMS protocol (K40, M2F-LR Pro…).
            $table->string('protocol', 20)->default('native')->after('type');
        });

        // Outbound command queue for ADMS devices. ZKTeco hardware polls
        // /iclock/getrequest and executes whatever we hand back — this is
        // how member enrolment and revocation reach the door.
        Schema::create('device_commands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('access_device_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('sequence'); // the C:<n>: id echoed back by the device
            $table->text('command');
            $table->string('kind', 30); // enrol / revoke / clear / custom — for the audit trail
            $table->foreignUuid('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending'); // pending | sent | acked | failed
            $table->string('device_return')->nullable(); // Return= code from the device
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acked_at')->nullable();
            $table->timestamps();

            $table->index(['access_device_id', 'status']);
            $table->unique(['access_device_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_commands');

        Schema::table('access_devices', function (Blueprint $table) {
            $table->dropColumn('protocol');
        });
    }
};
