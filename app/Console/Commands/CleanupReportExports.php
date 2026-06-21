<?php

namespace App\Console\Commands;

use App\Domain\Reporting\ReportExport;
use App\Enums\ReportExportStatus;
use App\Support\Audit\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupReportExports extends Command
{
    protected $signature = 'reports:cleanup-expired {--dry-run : Show expired exports without deleting files}';

    protected $description = 'Expire report exports and remove expired files.';

    public function handle(AuditLogger $auditLogger): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $exports = ReportExport::withoutGlobalScope('hotel')
            ->where('status', ReportExportStatus::COMPLETED->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($exports as $export) {
            if (! $dryRun && $export->file_disk && $export->file_path) {
                Storage::disk($export->file_disk)->delete($export->file_path);
            }

            if (! $dryRun) {
                $export->update(['status' => ReportExportStatus::EXPIRED, 'progress' => 100]);
                $auditLogger->record('report.export_expired', $export->hotel_id, $export->requested_by, $export, [
                    'report_type' => $export->report_type,
                    'format' => $export->format,
                ]);
            }
        }

        $this->info(($dryRun ? 'Would expire ' : 'Expired ').$exports->count().' report export(s).');

        return self::SUCCESS;
    }
}
