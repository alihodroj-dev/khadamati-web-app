<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'near_lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:near_lng'],
            'near_lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:near_lat'],
        ]);

        $query = Office::query()->where('is_active', true);

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
                ->sortBy(fn (Office $office) => $this->approximateDistanceSquared($office, $lat, $lng))
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

    private function approximateDistanceSquared(Office $office, float $lat, float $lng): float
    {
        if ($office->latitude === null || $office->longitude === null) {
            return PHP_FLOAT_MAX;
        }

        $deltaLat = (float) $office->latitude - $lat;
        $deltaLng = (float) $office->longitude - $lng;

        return ($deltaLat * $deltaLat) + ($deltaLng * $deltaLng);
    }
}
