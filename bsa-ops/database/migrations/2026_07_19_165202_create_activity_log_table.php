<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            // Both morphs are widened to hold a uuid. spatie's default
            // nullableMorphs() types these as bigint, which MySQL silently
            // truncates a uuid into (sqlite does not, so it only surfaces
            // against the real engine).
            //
            //   subject — always one of our uuid models (Invoice, Payment…)
            //   causer  — a staff User (bigint id) at the desk, but a
            //             Member (uuid) when the purchase comes through the
            //             member portal API. A char column holds both.
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 36)->nullable();
            $table->index(['subject_type', 'subject_id'], 'subject');

            $table->string('causer_type')->nullable();
            $table->string('causer_id', 36)->nullable();
            $table->index(['causer_type', 'causer_id'], 'causer');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}
