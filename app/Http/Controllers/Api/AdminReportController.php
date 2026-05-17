<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminReportService;
use App\Support\ReportFilters;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    use ApiResponse;

    public function overview(Request $request)
    {
        $filters = ReportFilters::fromRequest($request);

        return $this->successResponse(
            AdminReportService::overview($filters),
            'Report overview retrieved successfully.'
        );
    }
}
