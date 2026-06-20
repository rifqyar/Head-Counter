<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_events', function (Blueprint $table) {
            if (! Schema::hasColumn('meeting_events', 'meeting_qr_token_last_four')) {
                $table->string('meeting_qr_token_last_four', 4)->nullable()->after('meeting_qr_token_hash');
            }
            if (! Schema::hasColumn('meeting_events', 'meeting_qr_issued_at')) {
                $table->timestampTz('meeting_qr_issued_at')->nullable()->after('meeting_qr_token_last_four');
            }
            if (! Schema::hasColumn('meeting_events', 'meeting_qr_expires_at')) {
                $table->timestampTz('meeting_qr_expires_at')->nullable()->after('meeting_qr_issued_at');
            }
            if (! Schema::hasColumn('meeting_events', 'meeting_qr_revoked_at')) {
                $table->timestampTz('meeting_qr_revoked_at')->nullable()->after('meeting_qr_expires_at');
            }
            if (! Schema::hasColumn('meeting_events', 'meeting_qr_path')) {
                $table->string('meeting_qr_path')->nullable()->after('meeting_qr_revoked_at');
            }
        });

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS meeting_events_qr_token_hash_unique ON meeting_events (meeting_qr_token_hash) WHERE meeting_qr_token_hash IS NOT NULL');

        Schema::create('participant_qr_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->string('token_hash', 64);
            $table->string('token_last_four', 4);
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampTz('issued_at');
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->unique('token_hash');
            $table->index(['participant_id', 'status']);
        });

        DB::statement("CREATE UNIQUE INDEX participant_qr_credentials_one_active ON participant_qr_credentials (participant_id) WHERE status = 'ACTIVE'");

        Schema::create('meal_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->foreignId('meeting_event_id')->constrained('meeting_events')->cascadeOnDelete();
            $table->string('entitlement_type', 30);
            $table->unsignedInteger('session_number');
            $table->string('name');
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->string('location')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->index(['hotel_id', 'meeting_event_id']);
            $table->index(['meeting_event_id', 'status']);
        });

        DB::statement('ALTER TABLE meal_sessions ADD CONSTRAINT meal_sessions_time_range_check CHECK (starts_at IS NULL OR ends_at IS NULL OR ends_at > starts_at)');
        DB::statement('ALTER TABLE meal_sessions ADD CONSTRAINT meal_sessions_positive_number_check CHECK (session_number > 0)');
        DB::statement('CREATE UNIQUE INDEX meal_sessions_unique_session ON meal_sessions (meeting_event_id, entitlement_type, session_number)');

        Schema::create('participant_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('meeting_event_id')->constrained('meeting_events')->cascadeOnDelete();
            $table->string('entitlement_type', 30);
            $table->unsignedInteger('total_quantity')->default(0);
            $table->unsignedInteger('redeemed_quantity')->default(0);
            $table->unsignedInteger('remaining_quantity')->default(0);
            $table->timestampsTz();
            $table->unique(['participant_id', 'meeting_event_id', 'entitlement_type'], 'participant_entitlements_unique_type');
            $table->index(['meeting_event_id', 'entitlement_type']);
        });

        DB::statement('ALTER TABLE participant_entitlements ADD CONSTRAINT participant_entitlements_quantity_check CHECK (total_quantity >= 0 AND redeemed_quantity >= 0 AND remaining_quantity >= 0 AND remaining_quantity = total_quantity - redeemed_quantity)');

        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->foreignId('participant_id')->constrained('participants')->restrictOnDelete();
            $table->foreignId('meeting_event_id')->constrained('meeting_events')->restrictOnDelete();
            $table->foreignId('meal_session_id')->constrained('meal_sessions')->restrictOnDelete();
            $table->foreignId('participant_entitlement_id')->nullable()->constrained('participant_entitlements')->nullOnDelete();
            $table->foreignId('original_redemption_id')->nullable()->constrained('redemptions')->nullOnDelete();
            $table->string('redemption_number')->unique();
            $table->timestampTz('redeemed_at')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_id')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->string('status', 20);
            $table->string('rejection_code', 40)->nullable();
            $table->text('override_reason')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestampTz('created_at')->nullable();
            $table->index(['hotel_id', 'meeting_event_id']);
            $table->index(['participant_id', 'meal_session_id']);
            $table->index(['idempotency_key']);
            $table->index(['original_redemption_id']);
        });

        DB::statement("CREATE UNIQUE INDEX redemptions_one_active_success ON redemptions (participant_id, meal_session_id) WHERE status IN ('SUCCESS', 'OVERRIDDEN')");
        DB::statement("CREATE UNIQUE INDEX redemptions_rejected_idempotency_once ON redemptions (hotel_id, idempotency_key) WHERE status = 'REJECTED' AND idempotency_key IS NOT NULL");

        Schema::create('scanner_idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('idempotency_key');
            $table->string('request_hash', 64);
            $table->unsignedSmallInteger('response_status');
            $table->jsonb('response_body')->default('{}');
            $table->timestampTz('expires_at');
            $table->timestampTz('created_at')->nullable();
            $table->unique(['hotel_id', 'idempotency_key']);
            $table->index('expires_at');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestampTz('created_at')->nullable();
            $table->index(['hotel_id', 'event']);
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('scanner_idempotency_keys');
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('participant_entitlements');
        Schema::dropIfExists('meal_sessions');
        Schema::dropIfExists('participant_qr_credentials');

        DB::statement('DROP INDEX IF EXISTS meeting_events_qr_token_hash_unique');
        Schema::table('meeting_events', function (Blueprint $table) {
            foreach (['meeting_qr_token_last_four', 'meeting_qr_issued_at', 'meeting_qr_expires_at', 'meeting_qr_revoked_at', 'meeting_qr_path'] as $column) {
                if (Schema::hasColumn('meeting_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
