<?php

namespace App\Http\Controllers;

use App\Enums\ReportType;
use App\Http\Requests\ReportRequest;
use App\Services\DashboardFilterData;
use App\Services\ReportQueryService;
use App\Support\Audit\AuditLogger;
use App\Support\Reporting\HotelTimezoneService;
use App\Support\Reporting\ReportCatalog;
use App\Support\Reporting\ReportFilter;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index', ['reports' => ReportCatalog::types()]);
    }

    public function show(ReportRequest $request, string $report, ReportQueryService $queries, DashboardFilterData $filterData, HotelTimezoneService $timezones, AuditLogger $auditLogger)
    {
        abort_unless(ReportType::tryFrom($report), 404);

        $filter = ReportFilter::from($request->user(), $request->validated(), $timezones);
        $rows = $queries->rows($report, $filter, 500);
        $headings = $queries->headings($report, $filter);

        $auditLogger->record('report.viewed', $filter->hotel?->id, $request->user()->id, null, [
            'report_type' => $report,
            'filters' => $filter->filters,
        ]);

        return view('reports.show', [
            'report' => ReportType::from($report),
            'headings' => $headings,
            'rows' => $rows,
            'filters' => $filterData->build($request->user(), $filter),
            'activeFilters' => $filter->filters,
        ]);
    }
}
