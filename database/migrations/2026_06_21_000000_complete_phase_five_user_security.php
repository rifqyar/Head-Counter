<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('ACTIVE')->after('password');
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestampTz('last_login_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('users', 'deactivated_at')) {
                $table->timestampTz('deactivated_at')->nullable()->after('last_login_at');
            }
            if (! Schema::hasColumn('users', 'deactivated_by')) {
                $table->foreignId('deactivated_by')->nullable()->after('deactivated_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deactivated_by')) {
                $table->dropConstrainedForeignId('deactivated_by');
            }
            foreach (['deactivated_at', 'last_login_at', 'status'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
