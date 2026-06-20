<?php

namespace App\Console\Commands;

use App\Actions\RedeemParticipantAction;
use Illuminate\Console\Command;

class RunConcurrentRedemptionWorker extends Command
{
    protected $signature = 'scanner:concurrent-redemption-worker
        {payload : JSON encoded scanner payload}
        {scanner_id : Scanner user id}
        {hotel_id : Hotel id}
        {barrier_dir : Directory used for ready/release files}
        {worker : Worker name}';

    protected $description = 'Internal Phase 4 test worker for true process-based concurrent redemption.';

    public function handle(RedeemParticipantAction $action): int
    {
        $payload = json_decode($this->argument('payload'), true, flags: JSON_THROW_ON_ERROR);
        $barrierDir = (string) $this->argument('barrier_dir');
        $worker = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $this->argument('worker')) ?: uniqid('worker');

        if (! is_dir($barrierDir)) {
            mkdir($barrierDir, 0777, true);
        }

        file_put_contents($barrierDir.DIRECTORY_SEPARATOR.$worker.'.ready', '1');
        $release = $barrierDir.DIRECTORY_SEPARATOR.'release';
        $deadline = microtime(true) + 20;

        while (! file_exists($release)) {
            if (microtime(true) > $deadline) {
                $this->line(json_encode(['status' => 408, 'body' => ['message' => 'Barrier timeout']]));

                return self::FAILURE;
            }
            usleep(20000);
        }

        $result = $action->execute($payload, (int) $this->argument('scanner_id'), (int) $this->argument('hotel_id'));
        $this->line(json_encode($result));

        return self::SUCCESS;
    }
}
