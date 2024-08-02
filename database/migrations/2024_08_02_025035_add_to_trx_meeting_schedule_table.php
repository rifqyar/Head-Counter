<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trx_meeting_schedule', function (Blueprint $table) {
            $table->renameColumn('tgl_meeting', 'tgl_start');
            $table->date('tgl_end')->default(null)->nullable(true);
            $table->string('package')->default(null)->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trx_meeting_schedule', function (Blueprint $table) {
            //
        });
    }
};
