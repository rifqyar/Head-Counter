<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'actor_type')) {
                $table->string('actor_type')->nullable()->after('hotel_id');
            }
            if (! Schema::hasColumn('audit_logs', 'action')) {
                $table->string('action')->nullable()->after('event');
            }
            if (! Schema::hasColumn('audit_logs', 'entity_type')) {
                $table->string('entity_type')->nullable()->after('auditable_id');
            }
            if (! Schema::hasColumn('audit_logs', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
            }
            if (! Schema::hasColumn('audit_logs', 'before_data')) {
                $table->jsonb('before_data')->default('{}')->after('entity_id');
            }
            if (! Schema::hasColumn('audit_logs', 'after_data')) {
                $table->jsonb('after_data')->default('{}')->after('before_data');
            }
            if (! Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('metadata');
            }
            if (! Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            if (! Schema::hasColumn('audit_logs', 'request_id')) {
                $table->uuid('request_id')->nullable()->after('user_agent');
            }
        });

        DB::statement('UPDATE audit_logs SET action = event WHERE action IS NULL');
        DB::statement('UPDATE audit_logs SET entity_type = auditable_type, entity_id = auditable_id WHERE entity_type IS NULL');
        DB::statement("UPDATE audit_logs SET actor_type = 'user' WHERE actor_id IS NOT NULL AND actor_type IS NULL");
        DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_action_index ON audit_logs (action)');
        DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_request_id_index ON audit_logs (request_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_entity_index ON audit_logs (entity_type, entity_id)');

        Schema::create('integration_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->string('name');
            $table->string('key_prefix', 16)->unique();
            $table->string('secret_hash');
            $table->jsonb('abilities')->default('[]');
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampTz('last_used_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->index(['hotel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_api_keys');

        Schema::table('audit_logs', function (Blueprint $table) {
            foreach (['actor_type', 'action', 'entity_type', 'entity_id', 'before_data', 'after_data', 'ip_address', 'user_agent', 'request_id'] as $column) {
                if (Schema::hasColumn('audit_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
