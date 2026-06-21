<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('report_type', 50);
            $table->string('format', 10);
            $table->jsonb('filters')->default('{}');
            $table->string('status', 20)->default('PENDING');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('file_disk')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedInteger('row_count')->nullable();
            $table->text('error_message')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampsTz();
            $table->index(['hotel_id', 'report_type']);
            $table->index(['requested_by', 'status']);
            $table->index(['status', 'expires_at']);
        });

        DB::statement('CREATE INDEX IF NOT EXISTS meeting_events_hotel_start_status_index ON meeting_events (hotel_id, start_at, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS participants_hotel_meeting_status_index ON participants (hotel_id, meeting_event_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS meeting_attendances_meeting_participant_type_index ON meeting_attendances (meeting_event_id, participant_id, attendance_type)');
        DB::statement('CREATE INDEX IF NOT EXISTS meal_sessions_hotel_meeting_status_starts_index ON meal_sessions (hotel_id, meeting_event_id, status, starts_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS redemptions_hotel_meeting_session_status_redeemed_index ON redemptions (hotel_id, meeting_event_id, meal_session_id, status, redeemed_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS participant_entitlements_meeting_type_index ON participant_entitlements (meeting_event_id, entitlement_type)');
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
