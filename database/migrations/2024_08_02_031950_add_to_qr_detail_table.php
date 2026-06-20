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
        Schema::table('qr_detail', function (Blueprint $table) {
            $table->renameColumn('qr_valid', 'qr_valid_start');
            $table->dateTimeTz('qr_valid_end')->default(null)->nullable(true);
            $table->index('meeting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_detail', function (Blueprint $table) {
            $table->dropIndex(['meeting_id']);
            $table->dropColumn('qr_valid_end');
            $table->renameColumn('qr_valid_start', 'qr_valid');
        });
    }
};
