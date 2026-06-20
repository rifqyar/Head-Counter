<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditPostgresqlPhaseTwo extends Command
{
    protected $signature = 'db:audit-postgresql-phase2';

    protected $description = 'Audit the existing PostgreSQL database for Phase 2 data-quality and constraint readiness.';

    private array $orphanChecks = [
        'schedule_client_orphans' => ['trx_meeting_schedule', 'code_client', 'm_client', 'code'],
        'schedule_package_orphans' => ['trx_meeting_schedule', 'package', 'm_packages', 'kd_pck'],
        'schedule_room_orphans' => ['trx_meeting_schedule', 'room', 'm_meeting_rooms', 'kd_room'],
        'attendance_schedule_orphans' => ['trx_meeting_attendance', 'trx_metting_number', 'trx_meeting_schedule', 'trx_number'],
        'qr_schedule_orphans' => ['qr_detail', 'meeting_id', 'trx_meeting_schedule', 'id'],
        'room_status_orphans' => ['m_meeting_rooms', 'room_availability', 'r_room_status', 'kd_status'],
    ];

    private array $duplicateChecks = [
        'users' => 'username',
        'm_client' => 'code',
        'trx_meeting_schedule' => 'trx_number',
        'm_packages' => 'kd_pck',
        'm_meeting_rooms' => 'kd_room',
        'r_room_status' => 'kd_status',
    ];

    public function handle(): int
    {
        $this->info('Auditing PostgreSQL Phase 2 readiness.');
        $this->line('Connection: '.DB::connection()->getDriverName().' / '.DB::connection()->getDatabaseName());

        $failures = 0;

        foreach ($this->orphanChecks as $name => [$childTable, $childColumn, $parentTable, $parentColumn]) {
            $count = DB::table($childTable)
                ->leftJoin($parentTable, "{$childTable}.{$childColumn}", '=', "{$parentTable}.{$parentColumn}")
                ->whereNotNull("{$childTable}.{$childColumn}")
                ->whereNull("{$parentTable}.{$parentColumn}")
                ->count();

            $this->line("{$name}: {$count}");
            $failures += $count > 0 ? 1 : 0;
        }

        foreach ($this->duplicateChecks as $table => $column) {
            $count = DB::query()
                ->fromSub(
                    DB::table($table)
                        ->select($column)
                        ->groupBy($column)
                        ->havingRaw('COUNT(*) > 1'),
                    'duplicates'
                )
                ->count();

            $this->line("duplicate_{$table}_{$column}: {$count}");
            $failures += $count > 0 ? 1 : 0;
        }

        $missingForeignKeys = collect([
            'trx_meeting_schedule_code_client_foreign',
            'trx_meeting_schedule_package_foreign',
            'trx_meeting_schedule_room_foreign',
            'trx_meeting_attendance_trx_metting_number_foreign',
            'qr_detail_meeting_id_foreign',
            'm_meeting_rooms_room_availability_foreign',
        ])->reject(fn (string $constraint): bool => DB::table('pg_constraint')->where('conname', $constraint)->exists());

        foreach ($missingForeignKeys as $constraint) {
            $this->warn("missing_foreign_key: {$constraint}");
        }

        $failures += $missingForeignKeys->count();

        if ($failures > 0) {
            $this->error("Phase 2 audit found {$failures} blocking issue group(s).");

            return self::FAILURE;
        }

        $this->info('Phase 2 PostgreSQL audit passed.');

        return self::SUCCESS;
    }
}
