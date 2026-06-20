<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addForeignIfClean(
            'trx_meeting_schedule_code_client_foreign',
            'trx_meeting_schedule',
            'code_client',
            'm_client',
            'code',
            'ALTER TABLE trx_meeting_schedule ADD CONSTRAINT trx_meeting_schedule_code_client_foreign FOREIGN KEY (code_client) REFERENCES m_client (code) ON DELETE RESTRICT ON UPDATE CASCADE'
        );

        $this->addForeignIfClean(
            'trx_meeting_schedule_package_foreign',
            'trx_meeting_schedule',
            'package',
            'm_packages',
            'kd_pck',
            'ALTER TABLE trx_meeting_schedule ADD CONSTRAINT trx_meeting_schedule_package_foreign FOREIGN KEY (package) REFERENCES m_packages (kd_pck) ON DELETE SET NULL ON UPDATE CASCADE'
        );

        $this->addForeignIfClean(
            'trx_meeting_schedule_room_foreign',
            'trx_meeting_schedule',
            'room',
            'm_meeting_rooms',
            'kd_room',
            'ALTER TABLE trx_meeting_schedule ADD CONSTRAINT trx_meeting_schedule_room_foreign FOREIGN KEY (room) REFERENCES m_meeting_rooms (kd_room) ON DELETE SET NULL ON UPDATE CASCADE'
        );

        $this->addForeignIfClean(
            'trx_meeting_attendance_trx_metting_number_foreign',
            'trx_meeting_attendance',
            'trx_metting_number',
            'trx_meeting_schedule',
            'trx_number',
            'ALTER TABLE trx_meeting_attendance ADD CONSTRAINT trx_meeting_attendance_trx_metting_number_foreign FOREIGN KEY (trx_metting_number) REFERENCES trx_meeting_schedule (trx_number) ON DELETE RESTRICT ON UPDATE CASCADE'
        );

        $this->addForeignIfClean(
            'qr_detail_meeting_id_foreign',
            'qr_detail',
            'meeting_id',
            'trx_meeting_schedule',
            'id',
            'ALTER TABLE qr_detail ADD CONSTRAINT qr_detail_meeting_id_foreign FOREIGN KEY (meeting_id) REFERENCES trx_meeting_schedule (id) ON DELETE RESTRICT ON UPDATE CASCADE'
        );

        $this->addForeignIfClean(
            'm_meeting_rooms_room_availability_foreign',
            'm_meeting_rooms',
            'room_availability',
            'r_room_status',
            'kd_status',
            'ALTER TABLE m_meeting_rooms ADD CONSTRAINT m_meeting_rooms_room_availability_foreign FOREIGN KEY (room_availability) REFERENCES r_room_status (kd_status) ON DELETE RESTRICT ON UPDATE CASCADE'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE m_meeting_rooms DROP CONSTRAINT IF EXISTS m_meeting_rooms_room_availability_foreign');
        DB::statement('ALTER TABLE qr_detail DROP CONSTRAINT IF EXISTS qr_detail_meeting_id_foreign');
        DB::statement('ALTER TABLE trx_meeting_attendance DROP CONSTRAINT IF EXISTS trx_meeting_attendance_trx_metting_number_foreign');
        DB::statement('ALTER TABLE trx_meeting_schedule DROP CONSTRAINT IF EXISTS trx_meeting_schedule_room_foreign');
        DB::statement('ALTER TABLE trx_meeting_schedule DROP CONSTRAINT IF EXISTS trx_meeting_schedule_package_foreign');
        DB::statement('ALTER TABLE trx_meeting_schedule DROP CONSTRAINT IF EXISTS trx_meeting_schedule_code_client_foreign');
    }

    private function addForeignIfClean(
        string $constraint,
        string $childTable,
        string $childColumn,
        string $parentTable,
        string $parentColumn,
        string $sql
    ): void {
        if ($this->constraintExists($constraint)) {
            return;
        }

        $orphans = DB::table($childTable)
            ->leftJoin($parentTable, "{$childTable}.{$childColumn}", '=', "{$parentTable}.{$parentColumn}")
            ->whereNotNull("{$childTable}.{$childColumn}")
            ->whereNull("{$parentTable}.{$parentColumn}")
            ->count();

        if ($orphans > 0) {
            fwrite(STDERR, "Skipping {$constraint}: {$orphans} orphan row(s) found in {$childTable}.{$childColumn}.\n");

            return;
        }

        DB::statement($sql);
    }

    private function constraintExists(string $constraint): bool
    {
        return DB::table('pg_constraint')
            ->where('conname', $constraint)
            ->exists();
    }
};
