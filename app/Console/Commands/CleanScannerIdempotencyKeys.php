<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanScannerIdempotencyKeys extends Command
{
    protected $signature = 'scanner:idempotency-cleanup {--dry-run}';

    protected $description = 'Delete expired scanner idempotency keys.';

    public function handle(): int
    {
        $query = DB::table('scanner_idempotency_keys')->where('expires_at', '<', now());
        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info($count.' expired scanner idempotency keys would be deleted.');

            return self::SUCCESS;
        }

        $query->delete();
        $this->info($count.' expired scanner idempotency keys deleted.');

        return self::SUCCESS;
    }
}
