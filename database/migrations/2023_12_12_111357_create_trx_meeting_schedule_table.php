<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP VIEW IF EXISTS trx_meeting_schedule CASCADE');
        }

        Schema::create('trx_meeting_schedule', function (Blueprint $table) {
            $table->id();
            $table->string('trx_number')->unique();
            $table->string('code_client', 3);
            $table->date('tgl_meeting');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('kuota');
            $table->string('qr_path');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_meeting_schedule');
    }
};
