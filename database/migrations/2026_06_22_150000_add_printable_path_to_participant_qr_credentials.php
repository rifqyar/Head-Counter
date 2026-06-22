<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_qr_credentials', function (Blueprint $table) {
            if (! Schema::hasColumn('participant_qr_credentials', 'printable_path')) {
                $table->string('printable_path')->nullable()->after('token_last_four');
            }
        });
    }

    public function down(): void
    {
        Schema::table('participant_qr_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('participant_qr_credentials', 'printable_path')) {
                $table->dropColumn('printable_path');
            }
        });
    }
};
