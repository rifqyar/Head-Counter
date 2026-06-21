<?php

namespace App\Services;

use App\Domain\Reporting\ReportExport;
use App\Enums\ReportExportStatus;
use App\Exports\ArrayReportExport;
use App\Jobs\ExportReportJob;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use App\Support\Reporting\ReportFilter;
use App\Support\Reporting\SpreadsheetSafe;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function __construct(
        private readonly ReportQueryService $queries,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function request(string $type, string $format, ReportFilter $filter): mixed
    {
        $rowCount = $this->queries->count($type, $filter);
        $threshold = (int) config("reports.sync_thresholds.$format", 1000);

        if ($rowCount > $threshold) {
            $export = ReportExport::create([
                'hotel_id' => $filter->hotel?->id,
                'requested_by' => $filter->user->id,
                'report_type' => $type,
                'format' => $format,
                'filters' => $filter->filters,
                'status' => ReportExportStatus::PENDING,
                'progress' => 0,
                'row_count' => $rowCount,
                'expires_at' => now()->addDays((int) config('reports.export_expiration_days', 7)),
            ]);

            $this->auditLogger->record('report.export_requested', $filter->hotel?->id, $filter->user->id, $export, [
                'report_type' => $type,
                'format' => $format,
                'row_count' => $rowCount,
            ]);

            ExportReportJob::dispatch($export->id);

            return redirect()->route('reports.exports.index')->with('status', 'Large export queued. It will appear here when ready.');
        }

        $rows = $this->queries->rows($type, $filter)->all();
        $headings = $this->queries->headings($type, $filter);
        $fileName = $this->safeFileName($type, $format);

        $this->auditLogger->record('report.export_requested', $filter->hotel?->id, $filter->user->id, null, [
            'report_type' => $type,
            'format' => $format,
            'row_count' => count($rows),
            'mode' => 'sync',
        ]);

        return match ($format) {
            'xlsx' => Excel::download(new ArrayReportExport($headings, $rows), $fileName),
            'csv' => $this->csvDownload($headings, $rows, $fileName),
            'pdf' => Pdf::loadView('reports.pdf', ['title' => $type, 'headings' => $headings, 'rows' => $rows, 'filter' => $filter])->setPaper('a4', 'landscape')->download($fileName),
        };
    }

    public function generateQueued(ReportExport $export, User $user, ReportFilter $filter): void
    {
        $export->update(['status' => ReportExportStatus::PROCESSING, 'progress' => 5, 'started_at' => now(), 'error_message' => null]);
        $this->auditLogger->record('report.export_started', $export->hotel_id, $user->id, $export, ['report_type' => $export->report_type, 'format' => $export->format]);

        $rows = $this->queries->rows($export->report_type, $filter)->all();
        $headings = $this->queries->headings($export->report_type, $filter);
        $fileName = $this->safeFileName($export->report_type, $export->format);
        $path = 'reports/exports/'.$export->id.'/'.$fileName;
        Storage::disk('local')->deleteDirectory('reports/exports/'.$export->id);

        match ($export->format) {
            'xlsx' => Excel::store(new ArrayReportExport($headings, $rows), $path, 'local'),
            'csv' => $this->storeCsv($headings, $rows, $path),
            'pdf' => Storage::disk('local')->put($path, Pdf::loadView('reports.pdf', ['title' => $export->report_type, 'headings' => $headings, 'rows' => $rows, 'filter' => $filter])->setPaper('a4', 'landscape')->output()),
        };

        $export->update([
            'status' => ReportExportStatus::COMPLETED,
            'progress' => 100,
            'file_disk' => 'local',
            'file_path' => $path,
            'file_name' => $fileName,
            'row_count' => count($rows),
            'completed_at' => now(),
            'expires_at' => now()->addDays((int) config('reports.export_expiration_days', 7)),
        ]);
        $this->auditLogger->record('report.export_completed', $export->hotel_id, $user->id, $export, ['row_count' => count($rows)]);
    }

    public function csvDownload(array $headings, array $rows, string $fileName): StreamedResponse
    {
        return Response::streamDownload(function () use ($headings, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headings);
            foreach ($rows as $row) {
                fputcsv($handle, SpreadsheetSafe::row($row));
            }
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function storeCsv(array $headings, array $rows, string $path): void
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, $headings);
        foreach ($rows as $row) {
            fputcsv($stream, SpreadsheetSafe::row($row));
        }
        rewind($stream);
        Storage::disk('local')->put($path, stream_get_contents($stream));
        fclose($stream);
    }

    private function safeFileName(string $type, string $format): string
    {
        return Str::slug($type).'-'.now()->format('Ymd-His').'.'.$format;
    }
}
