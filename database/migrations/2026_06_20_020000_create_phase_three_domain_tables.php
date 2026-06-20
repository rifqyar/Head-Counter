<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('status', 20)->default('ACTIVE');
            $table->jsonb('settings')->default('{}');
            $table->timestampsTz();
        });

        DB::table('hotels')->insert([
            'code' => 'ORIA',
            'name' => 'Oria Hotel Jakarta',
            'address' => 'Jl. K.H. Wahid Hasyim No. 85, Jakarta Pusat 10350, Indonesia',
            'timezone' => 'Asia/Jakarta',
            'status' => 'ACTIVE',
            'settings' => json_encode(['source' => 'phase_3_default', 'website' => 'https://oriahotel.com/']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained('hotels')->nullOnDelete();
        });

        DB::table('users')->whereNull('hotel_id')->update(['hotel_id' => DB::table('hotels')->where('code', 'ORIA')->value('id')]);

        Schema::create('meeting_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->string('floor')->nullable();
            $table->unsignedInteger('capacity')->default(0);
            $table->string('operational_status', 30)->default('AVAILABLE');
            $table->jsonb('facilities')->default('{}');
            $table->timestampsTz();
            $table->unique(['hotel_id', 'code']);
            $table->index(['hotel_id', 'operational_status']);
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->string('external_id')->nullable();
            $table->string('company_name');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('tax_number')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();
            $table->unique(['hotel_id', 'external_id']);
            $table->index(['hotel_id', 'company_name']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->string('external_booking_id')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('booking_number');
            $table->string('booking_source')->default('LEGACY');
            $table->date('booking_date')->nullable();
            $table->string('status', 30)->default('CONFIRMED');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->unique(['hotel_id', 'booking_number']);
            $table->index(['hotel_id', 'client_id']);
            $table->index(['hotel_id', 'booking_date']);
            $table->index(['hotel_id', 'status']);
        });

        DB::statement('CREATE UNIQUE INDEX bookings_external_reference_unique ON bookings (hotel_id, booking_source, external_booking_id) WHERE external_booking_id IS NOT NULL');

        Schema::create('meeting_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('meeting_room_id')->nullable()->constrained('meeting_rooms')->nullOnDelete();
            $table->string('event_name');
            $table->date('event_date');
            $table->timestampTz('start_at');
            $table->timestampTz('end_at');
            $table->unsignedInteger('expected_participants')->default(0);
            $table->unsignedInteger('actual_participants')->default(0);
            $table->string('status', 30)->default('SCHEDULED');
            $table->string('legacy_trx_number')->nullable();
            $table->string('meeting_qr_token_hash')->nullable();
            $table->timestampTz('checkin_open_at')->nullable();
            $table->timestampTz('checkin_close_at')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
            $table->unique(['hotel_id', 'legacy_trx_number']);
            $table->index(['hotel_id', 'event_date']);
            $table->index(['hotel_id', 'status']);
            $table->index(['meeting_room_id', 'start_at', 'end_at']);
        });

        DB::statement('ALTER TABLE meeting_events ADD CONSTRAINT meeting_events_time_range_check CHECK (end_at > start_at)');
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
        DB::statement("ALTER TABLE meeting_events ADD CONSTRAINT meeting_events_room_time_exclusion EXCLUDE USING gist (meeting_room_id WITH =, tstzrange(start_at, end_at, '[)') WITH &&) WHERE (meeting_room_id IS NOT NULL AND status NOT IN ('CANCELLED', 'NO_SHOW'))");

        Schema::create('meeting_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();
            $table->unique(['hotel_id', 'code']);
        });

        Schema::create('package_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('meeting_packages')->cascadeOnDelete();
            $table->string('entitlement_type', 30);
            $table->unsignedInteger('quantity')->default(1);
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();
            $table->index(['package_id', 'entitlement_type']);
        });

        Schema::create('meeting_package_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_event_id')->constrained('meeting_events')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('meeting_packages')->restrictOnDelete();
            $table->unsignedInteger('participant_quota')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->unique(['meeting_event_id', 'package_id']);
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->foreignId('meeting_event_id')->constrained('meeting_events')->cascadeOnDelete();
            $table->string('participant_number');
            $table->string('full_name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('normalized_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('normalized_phone')->nullable();
            $table->string('identity_reference')->nullable();
            $table->string('registration_source')->default('LEGACY');
            $table->string('status', 30)->default('REGISTERED');
            $table->timestampTz('registered_at')->nullable();
            $table->timestampTz('checked_in_at')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();
            $table->unique(['meeting_event_id', 'participant_number']);
            $table->index(['hotel_id', 'meeting_event_id']);
        });

        DB::statement('CREATE UNIQUE INDEX participants_email_unique_per_meeting ON participants (meeting_event_id, normalized_email) WHERE normalized_email IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX participants_phone_unique_per_meeting ON participants (meeting_event_id, normalized_phone) WHERE normalized_phone IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX participants_identity_unique_per_meeting ON participants (meeting_event_id, identity_reference) WHERE identity_reference IS NOT NULL');

        Schema::create('meeting_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_event_id')->constrained('meeting_events')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->string('attendance_type', 30);
            $table->timestampTz('attended_at');
            $table->string('verification_method')->default('LEGACY');
            $table->string('device_id')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('metadata')->default('{}');
            $table->timestampTz('created_at')->nullable();
            $table->index(['meeting_event_id', 'attendance_type']);
        });

        DB::statement("CREATE UNIQUE INDEX meeting_attendances_unique_checkin ON meeting_attendances (participant_id) WHERE attendance_type = 'MEETING_CHECKIN'");
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_attendances');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('meeting_package_assignments');
        Schema::dropIfExists('package_entitlements');
        Schema::dropIfExists('meeting_packages');
        Schema::dropIfExists('meeting_events');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('meeting_rooms');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hotel_id');
        });

        Schema::dropIfExists('hotels');
    }
};
