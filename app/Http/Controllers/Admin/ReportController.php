<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use App\Models\Office;
use App\Services\AdminReportService;
use App\Support\ReportFilters;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = ReportFilters::fromRequest($request);
        $report = AdminReportService::overview($filters);

        return view('admin.reports.index', [
            'report' => $report,
            'filters' => $filters,
            'offices' => Office::query()->with('municipality')->orderBy('name')->get(),
            'municipalities' => Municipality::query()->orderBy('name')->get(),
        ]);
    }
}
