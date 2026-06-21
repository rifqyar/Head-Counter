<?php

namespace App\Jobs;

use App\Domain\Reporting\ReportExport;
use App\Enums\ReportExportStatus;
use App\Models\User;
use App\Services\ReportExportService;
use App\Support\Audit\AuditLogger;
use App\Support\Reporting\HotelTimezoneService;
use App\Support\Reporting\ReportFilter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ExportReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(private readonly int $exportId)
    {
        $this->onQueue('default');
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(ReportExportService $exports, HotelTimezoneService $timezones, AuditLogger $auditLogger): void
    {
        $export = ReportExport::withoutGlobalScope('hotel')->findOrFail($this->exportId);
        if ($export->status === ReportExportStatus::COMPLETED) {
            return;
        }

        $user = User::findOrFail($export->requested_by);
        $filters = $export->filters ?: [];
        if ($export->hotel_id) {
            $filters['hotel_id'] = $export->hotel_id;
        }

        try {
            $exports->generateQueued($export, $user, ReportFilter::from($user, $filters, $timezones));
        } catch (Throwable $exception) {
            $export->update([
                'status' => ReportExportStatus::FAILED,
                'progress' => 100,
                'error_message' => str($exception->getMessage())->limit(500)->toString(),
                'completed_at' => now(),
            ]);
            $auditLogger->record('report.export_failed', $export->hotel_id, $user->id, $export, ['error' => $export->error_message]);
            throw $exception;
        }
    }
}
