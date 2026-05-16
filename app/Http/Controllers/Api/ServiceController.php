<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Service::query()
            ->where('is_active', true)
            ->with(['category', 'office']);

        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->filled('category_id')) {
            $query->where('service_category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            [
                'services' => ServiceResource::collection($services),
            ],
            'Services retrieved successfully'
        );
    }

    public function show(Service $service)
    {
        if (! $service->is_active) {
            return $this->errorResponse(
                'Service not found.',
                null,
                404
            );
        }

        $service->load(['category', 'office']);

        return $this->successResponse(
            [
                'service' => new ServiceResource($service),
            ],
            'Service retrieved successfully'
        );
    }
}
