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
            $table->index('code_client');
            $table->index('tgl_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trx_meeting_schedule', function (Blueprint $table) {
            $table->dropIndex(['tgl_start']);
            $table->dropIndex(['code_client']);
            $table->dropColumn(['tgl_end', 'package']);
            $table->renameColumn('tgl_start', 'tgl_meeting');
        });
    }
};
