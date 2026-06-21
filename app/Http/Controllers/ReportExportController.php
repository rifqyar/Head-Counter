<?php

namespace App\Http\Controllers;

use App\Domain\Reporting\ReportExport;
use App\Enums\ReportExportStatus;
use App\Enums\ReportType;
use App\Http\Requests\ExportReportRequest;
use App\Services\ReportExportService;
use App\Support\Audit\AuditLogger;
use App\Support\Reporting\HotelTimezoneService;
use App\Support\Reporting\ReportFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportExportController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportExport::query()->with(['hotel', 'requester'])->latest();
        if (! $request->user()->isSuperAdmin()) {
            $query->where('requested_by', $request->user()->id);
        }

        return view('reports.exports', ['exports' => $query->paginate(25)]);
    }

    public function store(ExportReportRequest $request, string $report, ReportExportService $exports, HotelTimezoneService $timezones)
    {
        abort_unless(ReportType::tryFrom($report), 404);

        $validated = $request->validated();
        $format = $validated['format'];
        unset($validated['format']);

        return $exports->request($report, $format, ReportFilter::from($request->user(), $validated, $timezones));
    }

    public function download(Request $request, ReportExport $export, AuditLogger $auditLogger)
    {
        abort_unless($request->user()->isSuperAdmin() || $export->requested_by === $request->user()->id, 403);
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('report.export'), 403);
        abort_unless($export->status === ReportExportStatus::COMPLETED, 404);
        abort_if($export->expires_at && $export->expires_at->isPast(), 410);
        abort_unless($export->file_disk && $export->file_path && Storage::disk($export->file_disk)->exists($export->file_path), 404);

        $auditLogger->record('report.export_downloaded', $export->hotel_id, $request->user()->id, $export, [
            'report_type' => $export->report_type,
            'format' => $export->format,
            'row_count' => $export->row_count,
        ]);

        return Storage::disk($export->file_disk)->download($export->file_path, basename($export->file_name));
    }
}
