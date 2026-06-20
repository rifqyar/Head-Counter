<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateMysqlToPostgresql extends Command
{
    protected $signature = 'db:migrate-mysql-to-pgsql
        {--source=mysql_legacy : Source MySQL connection name}
        {--target=pgsql : Target PostgreSQL connection name}
        {--chunk=500 : Rows per batch}
        {--dry-run : Validate and report without writing to PostgreSQL}';

    protected $description = 'Copy legacy MySQL data into PostgreSQL with Phase 2 validation and normalization.';

    private array $tables = [
        'permissions' => ['id'],
        'roles' => ['id'],
        'role_has_permissions' => ['permission_id', 'role_id'],
        'users' => ['id'],
        'm_client' => ['id'],
        'r_room_status' => ['id'],
        'm_meeting_rooms' => ['id'],
        'm_packages' => ['id'],
        'trx_meeting_schedule' => ['id'],
        'trx_meeting_attendance' => ['id'],
        'qr_detail' => ['id'],
        'model_has_permissions' => ['permission_id', 'model_id', 'model_type'],
        'model_has_roles' => ['role_id', 'model_id', 'model_type'],
        'personal_access_tokens' => ['id'],
        'failed_jobs' => ['id'],
    ];

    private array $statusMap = [
        '001' => 'AVAILABLE',
        'Available' => 'AVAILABLE',
        'AVAILABLE' => 'AVAILABLE',
        '002' => 'RESERVED',
        'Booked' => 'RESERVED',
        'Reserved' => 'RESERVED',
        'RESERVED' => 'RESERVED',
        '003' => 'OCCUPIED',
        'Occupied' => 'OCCUPIED',
        'OCCUPIED' => 'OCCUPIED',
        'CLEANING' => 'CLEANING',
        'MAINTENANCE' => 'MAINTENANCE',
        'INACTIVE' => 'INACTIVE',
    ];

    private array $errors = [];

    public function handle(): int
    {
        $source = DB::connection((string) $this->option('source'));
        $target = DB::connection((string) $this->option('target'));
        $chunk = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info(($dryRun ? 'Dry-run validating' : 'Migrating').' MySQL data to PostgreSQL.');

        foreach ($this->tables as $table => $uniqueBy) {
            if (! $this->tableExists($source, $table)) {
                $this->warn("Source table {$table} does not exist; skipped.");

                continue;
            }

            if (! $this->tableExists($target, $table)) {
                $this->warn("Target table {$table} does not exist; skipped.");

                continue;
            }

            $this->validateBusinessKeys($source, $table);
            $this->copyTable($source, $target, $table, $uniqueBy, $chunk, $dryRun);
        }

        $this->validateOrphans($source);

        if ($this->errors !== []) {
            foreach ($this->errors as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info('Migration validation completed without blocking errors.');

        return self::SUCCESS;
    }

    private function tableExists(ConnectionInterface $connection, string $table): bool
    {
        return $connection->getSchemaBuilder()->hasTable($table);
    }

    private function copyTable(
        ConnectionInterface $source,
        ConnectionInterface $target,
        string $table,
        array $uniqueBy,
        int $chunk,
        bool $dryRun
    ): void {
        $sourceCount = $source->table($table)->count();
        $this->line("{$table}: {$sourceCount} source rows.");

        $targetColumns = $target->getSchemaBuilder()->getColumnListing($table);
        $offset = 0;

        while ($offset < $sourceCount) {
            $rows = $source->table($table)->offset($offset)->limit($chunk)->get();
            $payload = [];

            foreach ($rows as $row) {
                $payload[] = $this->transformRow($table, (array) $row, $targetColumns);
            }

            if (! $dryRun && $payload !== []) {
                $target->table($table)->upsert($payload, $uniqueBy);
            }

            $offset += $chunk;
        }

        if (! $dryRun && in_array('id', $targetColumns, true)) {
            $this->resetPostgresSequence($target, $table);
        }

        if (! $dryRun) {
            $targetCount = $target->table($table)->count();
            $this->line("{$table}: {$targetCount} target rows after upsert.");
        }
    }

    private function transformRow(string $table, array $row, array $targetColumns): array
    {
        if ($table === 'users' && in_array('email', $targetColumns, true) && ! array_key_exists('email', $row)) {
            $row['email'] = null;
        }

        if ($table === 'r_room_status') {
            $row['kd_status'] = $this->mapStatus((string) ($row['kd_status'] ?? ''));
            $row['name'] = Str::title(Str::lower(str_replace('_', ' ', $row['kd_status'])));
        }

        if ($table === 'm_meeting_rooms') {
            $row['room_availability'] = $this->mapStatus((string) ($row['room_availability'] ?? ''));
        }

        if ($table === 'm_packages') {
            $row['price'] = $this->normalizePrice($row['price'] ?? null, (string) ($row['kd_pck'] ?? 'unknown'));
        }

        if ($table === 'trx_meeting_attendance' && array_key_exists('address', $row) && ! array_key_exists('jabatan', $row)) {
            $row['jabatan'] = $row['address'];
        }

        return array_intersect_key($row, array_flip($targetColumns));
    }

    private function mapStatus(string $status): string
    {
        if (isset($this->statusMap[$status])) {
            return $this->statusMap[$status];
        }

        $this->errors[] = "Unmapped room status value encountered: {$status}.";

        return $status;
    }

    private function normalizePrice(mixed $value, string $code): ?string
    {
        if ($value === null || $value === '') {
            $this->errors[] = "Package {$code} has an empty price.";

            return null;
        }

        $clean = trim((string) $value);
        $clean = preg_replace('/[^\d,.]/', '', $clean) ?? '';

        if ($clean === '') {
            $this->errors[] = "Package {$code} has an invalid price.";

            return null;
        }

        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{1,2})?$/', $clean)) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d{1,2})?$/', $clean)) {
            $clean = str_replace(',', '', $clean);
        } elseif (preg_match('/^\d+,\d{1,2}$/', $clean)) {
            $clean = str_replace(',', '.', $clean);
        }

        if (! is_numeric($clean)) {
            $this->errors[] = "Package {$code} price could not be normalized safely.";

            return null;
        }

        return number_format((float) $clean, 2, '.', '');
    }

    private function validateBusinessKeys(ConnectionInterface $source, string $table): void
    {
        $keys = [
            'users' => ['username'],
            'm_client' => ['code'],
            'trx_meeting_schedule' => ['trx_number'],
            'm_packages' => ['kd_pck'],
            'm_meeting_rooms' => ['kd_room'],
            'r_room_status' => ['kd_status'],
        ];

        foreach ($keys[$table] ?? [] as $column) {
            $duplicates = $source->table($table)
                ->select($column)
                ->groupBy($column)
                ->havingRaw('COUNT(*) > 1')
                ->count();

            if ($duplicates > 0) {
                $this->errors[] = "{$table}.{$column} has {$duplicates} duplicate key group(s).";
            }
        }
    }

    private function validateOrphans(ConnectionInterface $source): void
    {
        $checks = [
            ['trx_meeting_schedule', 'code_client', 'm_client', 'code'],
            ['trx_meeting_schedule', 'package', 'm_packages', 'kd_pck'],
            ['trx_meeting_schedule', 'room', 'm_meeting_rooms', 'kd_room'],
            ['trx_meeting_attendance', 'trx_metting_number', 'trx_meeting_schedule', 'trx_number'],
            ['qr_detail', 'meeting_id', 'trx_meeting_schedule', 'id'],
            ['m_meeting_rooms', 'room_availability', 'r_room_status', 'kd_status'],
        ];

        foreach ($checks as [$child, $childColumn, $parent, $parentColumn]) {
            if (! $this->tableExists($source, $child) || ! $this->tableExists($source, $parent)) {
                continue;
            }

            $orphans = $source->table($child)
                ->leftJoin($parent, "{$child}.{$childColumn}", '=', "{$parent}.{$parentColumn}")
                ->whereNotNull("{$child}.{$childColumn}")
                ->whereNull("{$parent}.{$parentColumn}")
                ->count();

            if ($orphans > 0) {
                $this->errors[] = "{$child}.{$childColumn} has {$orphans} orphan row(s).";
            }
        }
    }

    private function resetPostgresSequence(ConnectionInterface $target, string $table): void
    {
        $target->statement(
            "SELECT setval(pg_get_serial_sequence(?, 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1), true)",
            [$table]
        );
    }
}
