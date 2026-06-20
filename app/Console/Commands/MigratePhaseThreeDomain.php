<?php

namespace App\Console\Commands;

use Database\Seeders\PhaseThreeDomainSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePhaseThreeDomain extends Command
{
    protected $signature = 'headcounter:migrate-phase-three-domain
        {--dry-run : Validate and report without writing canonical tables}
        {--validate-only : Only run validation reports}
        {--batch=500 : Reserved batch size option for production runs}
        {--resume : Rerun idempotently from existing canonical rows}';

    protected $description = 'Migrate and validate legacy Phase 3 domain data into canonical multi-hotel tables.';

    public function handle(): int
    {
        $this->info('Phase 3 domain migration validation starting.');
        $this->line('Mode: '.($this->option('dry-run') ? 'dry-run' : ($this->option('validate-only') ? 'validate-only' : 'execute')));
        $this->line('Batch size: '.$this->option('batch').($this->option('resume') ? ' (resume enabled)' : ''));

        $before = $this->buildReport();
        $this->renderReport('Before migration', $before);

        if (! $this->option('dry-run') && ! $this->option('validate-only')) {
            DB::transaction(function () {
                app(PhaseThreeDomainSeeder::class)->run();
            });
            $this->info('Canonical Phase 3 data migration completed.');
        }

        $after = $this->buildReport();
        $this->renderReport($this->option('dry-run') ? 'Dry-run projected validation' : 'After migration', $after);

        return $after['failed_row_count'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function buildReport(): array
    {
        $counts = [
            'users' => ['users', 'users'],
            'rooms' => ['m_meeting_rooms', 'meeting_rooms'],
            'clients' => ['m_client', 'clients'],
            'packages' => ['m_packages', 'meeting_packages'],
            'meetings' => ['trx_meeting_schedule', 'meeting_events'],
            'participants' => ['trx_meeting_attendance', 'participants'],
            'attendance' => ['trx_meeting_attendance', 'meeting_attendances'],
            'package_assignments' => ['trx_meeting_schedule', 'meeting_package_assignments'],
        ];

        $rows = [];
        foreach ($counts as $name => [$source, $target]) {
            $sourceCount = $this->countTable($source);
            $targetCount = $this->countTable($target);
            $rows[$name] = [
                'source_row_count' => $sourceCount,
                'target_row_count' => $targetCount,
                'migrated_row_count' => min($sourceCount, $targetCount),
                'skipped_row_count' => max(0, $sourceCount - $targetCount),
            ];
        }

        $duplicates = [
            'legacy_client_code' => $this->duplicateCount('m_client', 'code'),
            'legacy_room_code' => $this->duplicateCount('m_meeting_rooms', 'kd_room'),
            'legacy_package_code' => $this->duplicateCount('m_packages', 'kd_pck'),
            'legacy_transaction_number' => $this->duplicateCount('trx_meeting_schedule', 'trx_number'),
            'canonical_client_external_id' => $this->duplicateCompositeCount('clients', ['hotel_id', 'external_id']),
            'canonical_room_code' => $this->duplicateCompositeCount('meeting_rooms', ['hotel_id', 'code']),
            'canonical_package_code' => $this->duplicateCompositeCount('meeting_packages', ['hotel_id', 'code']),
        ];

        $orphans = [
            'schedule_client_orphans' => $this->orphanCount('trx_meeting_schedule', 'code_client', 'm_client', 'code'),
            'schedule_package_orphans' => $this->orphanCount('trx_meeting_schedule', 'package', 'm_packages', 'kd_pck'),
            'schedule_room_orphans' => $this->orphanCount('trx_meeting_schedule', 'room', 'm_meeting_rooms', 'kd_room'),
            'attendance_schedule_orphans' => $this->orphanCount('trx_meeting_attendance', 'trx_metting_number', 'trx_meeting_schedule', 'trx_number'),
        ];

        $nulls = [
            'client_required_nulls' => $this->requiredNullCount('m_client', ['code', 'name']),
            'room_required_nulls' => $this->requiredNullCount('m_meeting_rooms', ['kd_room', 'name']),
            'package_required_nulls' => $this->requiredNullCount('m_packages', ['kd_pck', 'name', 'price']),
            'meeting_required_nulls' => $this->requiredNullCount('trx_meeting_schedule', ['trx_number', 'code_client', 'tgl_start', 'jam_mulai', 'jam_selesai']),
            'attendance_required_nulls' => $this->requiredNullCount('trx_meeting_attendance', ['trx_metting_number', 'name']),
        ];

        $unmappedStatus = DB::table('m_meeting_rooms')
            ->whereNotIn('room_availability', ['001', '002', '003', 'AVAILABLE', 'RESERVED', 'OCCUPIED', 'CLEANING', 'MAINTENANCE', 'INACTIVE', 'Available', 'Booked', 'Occupied'])
            ->count();

        $businessKeyMismatch = max(0, $this->countTable('trx_meeting_schedule') - DB::table('meeting_events')->whereNotNull('legacy_trx_number')->count());
        $failed = array_sum($duplicates) + array_sum($orphans) + array_sum($nulls) + $unmappedStatus;

        return [
            'rows' => $rows,
            'duplicate_count' => array_sum($duplicates),
            'duplicates' => $duplicates,
            'orphan_count' => array_sum($orphans),
            'orphans' => $orphans,
            'null_required_field_count' => array_sum($nulls),
            'nulls' => $nulls,
            'unmapped_status_count' => $unmappedStatus,
            'invalid_foreign_key_count' => array_sum($orphans),
            'business_key_mismatch_count' => $businessKeyMismatch,
            'failed_row_count' => $failed,
        ];
    }

    private function renderReport(string $title, array $report): void
    {
        $this->newLine();
        $this->info($title);
        $this->table(['Resource', 'Source', 'Target', 'Migrated', 'Skipped'], collect($report['rows'])->map(fn ($row, $name) => [
            $name,
            $row['source_row_count'],
            $row['target_row_count'],
            $row['migrated_row_count'],
            $row['skipped_row_count'],
        ])->values()->all());

        $this->line('Duplicate count: '.$report['duplicate_count']);
        $this->line('Orphan count: '.$report['orphan_count']);
        $this->line('Null-required-field count: '.$report['null_required_field_count']);
        $this->line('Unmapped status count: '.$report['unmapped_status_count']);
        $this->line('Invalid foreign-key count: '.$report['invalid_foreign_key_count']);
        $this->line('Business-key mismatch count: '.$report['business_key_mismatch_count']);
        $this->line('Failed row count: '.$report['failed_row_count']);
    }

    private function countTable(string $table): int
    {
        return DB::table($table)->count();
    }

    private function duplicateCount(string $table, string $column): int
    {
        return DB::table($table)
            ->select($column)
            ->groupBy($column)
            ->havingRaw('count(*) > 1')
            ->count();
    }

    private function duplicateCompositeCount(string $table, array $columns): int
    {
        return DB::table($table)
            ->select($columns)
            ->groupBy($columns)
            ->havingRaw('count(*) > 1')
            ->count();
    }

    private function orphanCount(string $sourceTable, string $sourceColumn, string $targetTable, string $targetColumn): int
    {
        return DB::table($sourceTable)
            ->leftJoin($targetTable, "{$targetTable}.{$targetColumn}", '=', "{$sourceTable}.{$sourceColumn}")
            ->whereNotNull("{$sourceTable}.{$sourceColumn}")
            ->whereNull("{$targetTable}.{$targetColumn}")
            ->count();
    }

    private function requiredNullCount(string $table, array $columns): int
    {
        return DB::table($table)
            ->where(function ($query) use ($table, $columns) {
                foreach ($columns as $column) {
                    $query->orWhereNull("{$table}.{$column}");
                }
            })
            ->count();
    }
}
