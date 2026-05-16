<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Service;
use App\Support\GeoDistance;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'service_id' => [
                'nullable',
                'integer',
                Rule::exists('services', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('service_categories', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'near_lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:near_lng'],
            'near_lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:near_lat'],
        ]);

        $query = Office::query()->where('is_active', true);

        if (! empty($validated['service_id'])) {
            $service = Service::query()
                ->where('is_active', true)
                ->findOrFail($validated['service_id']);

            if ($service->office_id !== null) {
                $query->where('id', $service->office_id);
            }
        }

        if (! empty($validated['category_id'])) {
            $categoryId = (int) $validated['category_id'];

            $query->whereHas('services', function ($servicesQuery) use ($categoryId) {
                $servicesQuery
                    ->where('service_category_id', $categoryId)
                    ->where('is_active', true);
            });
        }

        if (! empty($validated['search'])) {
            $term = $validated['search'];
            $query->where(function ($searchQuery) use ($term) {
                $searchQuery
                    ->where('name', 'like', '%'.$term.'%')
                    ->orWhere('address', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%');
            });
        }

        $offices = $query->get();

        if (isset($validated['near_lat'], $validated['near_lng'])) {
            $lat = (float) $validated['near_lat'];
            $lng = (float) $validated['near_lng'];

            $offices = $offices
                ->map(function (Office $office) use ($lat, $lng) {
                    $office->setAttribute(
                        'distance_km',
                        $this->distanceKmForOffice($office, $lat, $lng)
                    );

                    return $office;
                })
                ->sortBy(fn (Office $office) => $office->distance_km ?? PHP_FLOAT_MAX)
                ->values();
        } else {
            $offices = $offices->sortBy('name')->values();
        }

        return $this->successResponse(
            [
                'offices' => OfficeResource::collection($offices),
            ],
            'Offices retrieved successfully.'
        );
    }

    public function show(Office $office)
    {
        if (! $office->is_active) {
            return $this->errorResponse(
                'Office not found.',
                null,
                404
            );
        }

        return $this->successResponse(
            [
                'office' => new OfficeResource($office),
            ],
            'Office retrieved successfully.'
        );
    }

    private function distanceKmForOffice(Office $office, float $lat, float $lng): ?float
    {
        if ($office->latitude === null || $office->longitude === null) {
            return null;
        }

        return round(
            GeoDistance::haversineKm(
                $lat,
                $lng,
                (float) $office->latitude,
                (float) $office->longitude
            ),
            2
        );
    }
}
