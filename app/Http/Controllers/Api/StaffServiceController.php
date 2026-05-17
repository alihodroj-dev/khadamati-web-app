<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceCategoryResource;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\ServiceValidation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class StaffServiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Service::class);

        $user = $request->user();

        $query = Service::query()
            ->with(['category', 'office'])
            ->where('office_id', $user->office_id)
            ->latest();

        return $this->successResponse(
            ServiceResource::collection($query->get()),
            'Office services retrieved successfully.'
        );
    }

    public function show(Request $request, Service $service)
    {
        $this->authorize('view', $service);

        $service->load(['category', 'office']);

        return $this->successResponse(
            new ServiceResource($service),
            'Service retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Service::class);

        $service = Service::query()->create(
            ServiceValidation::validate($request, $request->user())
        );
        $service->load(['category', 'office']);

        return $this->successResponse(
            new ServiceResource($service),
            'Service created successfully.',
            201
        );
    }

    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $service->update(
            ServiceValidation::validate($request, $request->user(), partial: true, service: $service)
        );
        $service->load(['category', 'office']);

        return $this->successResponse(
            new ServiceResource($service),
            'Service updated successfully.'
        );
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);

        if ($service->serviceRequests()->exists()) {
            return $this->errorResponse(
                'Service has service requests attached.',
                null,
                422
            );
        }

        $service->delete();

        return $this->successResponse(null, 'Service deleted successfully.');
    }

    public function categories(Request $request)
    {
        $this->authorize('viewAny', ServiceCategory::class);

        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->withCount([
                'services' => function ($query) use ($request) {
                    if ($request->user()->isStaff() && $request->user()->office_id) {
                        $query->where('office_id', $request->user()->office_id);
                    }
                },
            ])
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            ServiceCategoryResource::collection($categories),
            'Service categories retrieved successfully.'
        );
    }
}
