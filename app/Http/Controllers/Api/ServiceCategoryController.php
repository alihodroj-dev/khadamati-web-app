<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceCategoryResource;
use App\Models\ServiceCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->withCount('services')
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            'Service categories retrieved successfully',
            [
                'categories' => ServiceCategoryResource::collection($categories),
            ]
        );
    }

    public function show(ServiceCategory $serviceCategory)
    {
        if (! $serviceCategory->is_active) {
            return $this->errorResponse(
                'Service category not found.',
                null,
                404
            );
        }

        $serviceCategory->load([
            'services' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            },
        ]);

        return $this->successResponse(
            'Service category retrieved successfully',
            [
                'category' => new ServiceCategoryResource($serviceCategory),
            ]
        );
    }
}
