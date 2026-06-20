<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('email')->nullable()->after('username');
            });
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $this->normalizePackagePrice();
        $this->normalizeRoomStatuses();
        $this->alignQrForeignKeyType();
        $this->createIndexes();
        $this->useTimezoneAwareTimestamps();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data-preserving compatibility migration. Earlier table migrations still own drops.
    }

    private function normalizePackagePrice(): void
    {
        if (! Schema::hasTable('m_packages') || ! Schema::hasColumn('m_packages', 'price')) {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE m_packages
            ALTER COLUMN price TYPE numeric(15, 2)
            USING (
                CASE
                    WHEN price IS NULL OR btrim(price::text) = '' THEN NULL
                    WHEN btrim(price::text) ~ '^\d{1,3}(\.\d{3})+(,\d{1,2})?$'
                        THEN replace(replace(regexp_replace(price::text, '[^\d,.]', '', 'g'), '.', ''), ',', '.')::numeric
                    WHEN btrim(price::text) ~ '^\d{1,3}(,\d{3})+(\.\d{1,2})?$'
                        THEN replace(regexp_replace(price::text, '[^\d,.]', '', 'g'), ',', '')::numeric
                    WHEN btrim(price::text) ~ '^\d+,\d{1,2}$'
                        THEN replace(regexp_replace(price::text, '[^\d,.]', '', 'g'), ',', '.')::numeric
                    WHEN btrim(price::text) ~ '^\d+(\.\d{1,2})?$'
                        THEN btrim(price::text)::numeric
                    ELSE NULL
                END
            )
        SQL);
    }

    private function normalizeRoomStatuses(): void
    {
        if (Schema::hasTable('r_room_status')) {
            DB::table('r_room_status')->whereIn('kd_status', ['001', 'Available'])->update([
                'kd_status' => 'AVAILABLE',
                'name' => 'Available',
            ]);
            DB::table('r_room_status')->whereIn('kd_status', ['002', 'Booked', 'Reserved'])->update([
                'kd_status' => 'RESERVED',
                'name' => 'Reserved',
            ]);
            DB::table('r_room_status')->whereIn('kd_status', ['003', 'Occupied'])->update([
                'kd_status' => 'OCCUPIED',
                'name' => 'Occupied',
            ]);
        }

        if (Schema::hasTable('m_meeting_rooms')) {
            DB::table('m_meeting_rooms')->whereIn('room_availability', ['001', 'Available'])->update(['room_availability' => 'AVAILABLE']);
            DB::table('m_meeting_rooms')->whereIn('room_availability', ['002', 'Booked', 'Reserved'])->update(['room_availability' => 'RESERVED']);
            DB::table('m_meeting_rooms')->whereIn('room_availability', ['003', 'Occupied'])->update(['room_availability' => 'OCCUPIED']);
            DB::statement("ALTER TABLE m_meeting_rooms ALTER COLUMN room_availability SET DEFAULT 'AVAILABLE'");
        }
    }

    private function alignQrForeignKeyType(): void
    {
        if (Schema::hasTable('qr_detail') && Schema::hasColumn('qr_detail', 'meeting_id')) {
            DB::statement('ALTER TABLE qr_detail ALTER COLUMN meeting_id TYPE bigint USING meeting_id::bigint');
        }
    }

    private function createIndexes(): void
    {
        $statements = [
            'CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users (email)',
            'CREATE UNIQUE INDEX IF NOT EXISTS m_client_code_unique ON m_client (code)',
            'CREATE UNIQUE INDEX IF NOT EXISTS trx_meeting_schedule_trx_number_unique ON trx_meeting_schedule (trx_number)',
            'CREATE INDEX IF NOT EXISTS trx_meeting_schedule_code_client_index ON trx_meeting_schedule (code_client)',
            'CREATE INDEX IF NOT EXISTS trx_meeting_schedule_room_index ON trx_meeting_schedule (room)',
            'CREATE INDEX IF NOT EXISTS trx_meeting_schedule_tgl_start_index ON trx_meeting_schedule (tgl_start)',
            'CREATE UNIQUE INDEX IF NOT EXISTS m_packages_kd_pck_unique ON m_packages (kd_pck)',
            'CREATE UNIQUE INDEX IF NOT EXISTS m_meeting_rooms_kd_room_unique ON m_meeting_rooms (kd_room)',
            'CREATE UNIQUE INDEX IF NOT EXISTS r_room_status_kd_status_unique ON r_room_status (kd_status)',
            'CREATE INDEX IF NOT EXISTS qr_detail_meeting_id_index ON qr_detail (meeting_id)',
            'CREATE INDEX IF NOT EXISTS trx_meeting_attendance_trx_metting_number_index ON trx_meeting_attendance (trx_metting_number)',
        ];

        foreach ($statements as $statement) {
            DB::statement($statement);
        }
    }

    private function useTimezoneAwareTimestamps(): void
    {
        $columns = [
            'users' => ['created_at', 'updated_at'],
            'password_reset_tokens' => ['created_at'],
            'password_resets' => ['created_at'],
            'failed_jobs' => ['failed_at'],
            'personal_access_tokens' => ['last_used_at', 'expires_at', 'created_at', 'updated_at'],
            'permissions' => ['created_at', 'updated_at'],
            'roles' => ['created_at', 'updated_at'],
            'm_client' => ['created_at', 'updated_at'],
            'trx_meeting_schedule' => ['created_at', 'updated_at'],
            'trx_meeting_attendance' => ['created_at', 'updated_at'],
            'm_packages' => ['created_at', 'updated_at'],
            'm_meeting_rooms' => ['created_at', 'updated_at'],
            'r_room_status' => ['created_at', 'updated_at'],
            'qr_detail' => ['qr_valid_start', 'qr_valid_end', 'created_at', 'updated_at'],
        ];

        foreach ($columns as $table => $tableColumns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($tableColumns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE timestamptz USING {$column} AT TIME ZONE 'UTC'");
                }
            }
        }
    }
};
