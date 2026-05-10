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
        $query = Service::query()
            ->where('is_active', true)
            ->with('category');

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
            'Services retrieved successfully',
            [
                'services' => ServiceResource::collection($services),
            ]
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

        $service->load('category');

        return $this->successResponse(
            'Service retrieved successfully',
            [
                'service' => new ServiceResource($service),
            ]
        );
    }
}
