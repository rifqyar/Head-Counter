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
        Schema::table('trx_meeting_attendance', function (Blueprint $table) {
            $table->renameColumn('address', 'jabatan');
            $table->string('mac_address')->default(null)->nullable();
            $table->index('trx_metting_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trx_meeting_attendance', function (Blueprint $table) {
            $table->dropIndex(['trx_metting_number']);
            $table->dropColumn('mac_address');
            $table->renameColumn('jabatan', 'address');
        });
    }
};
