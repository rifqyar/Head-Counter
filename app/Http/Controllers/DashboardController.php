<?php

namespace App\Http\Controllers;

use App\Services\DashboardAlertService;
use App\Services\DashboardFilterData;
use App\Services\DashboardMetricsService;
use App\Support\Reporting\HotelTimezoneService;
use App\Support\Reporting\ReportFilter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return redirect('/home');
    }

    public function redirect(Request $request)
    {
        $redirectData = $request->session()->get('Redirect');
        if (isset($redirectData)) {
            return view('includes.redirect');
        } else {
            abort(404);
        }
    }

    public function dashboard(
        Request $request,
        DashboardMetricsService $metrics,
        DashboardAlertService $alerts,
        DashboardFilterData $filterData,
        HotelTimezoneService $timezones
    ) {
        $filter = ReportFilter::from($request->user(), $request->query(), $timezones);

        return view('dashboard', [
            'metrics' => $metrics->build($filter),
            'alerts' => $alerts->build($filter),
            'filters' => $filterData->build($request->user(), $filter),
            'activeFilters' => $filter->filters,
        ]);
    }
}
