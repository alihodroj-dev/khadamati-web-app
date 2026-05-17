<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MunicipalityResource;
use App\Models\Municipality;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MunicipalityController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $municipalities = Municipality::query()
            ->withCount('offices')
            ->latest()
            ->get();

        return $this->successResponse(
            MunicipalityResource::collection($municipalities),
            'Municipalities retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Municipality::class);

        $municipality = Municipality::query()->create($this->validateMunicipality($request));

        return $this->successResponse(
            new MunicipalityResource($municipality),
            'Municipality created successfully.',
            201
        );
    }

    public function update(Request $request, Municipality $municipality)
    {
        $this->authorize('update', $municipality);

        $municipality->update($this->validateMunicipality($request, true, $municipality));

        return $this->successResponse(
            new MunicipalityResource($municipality),
            'Municipality updated successfully.'
        );
    }

    public function destroy(Municipality $municipality)
    {
        $this->authorize('delete', $municipality);

        if ($municipality->offices()->exists()) {
            return $this->errorResponse('Municipality has offices attached.', null, 422);
        }

        $municipality->delete();

        return $this->successResponse(null, 'Municipality deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMunicipality(
        Request $request,
        bool $partial = false,
        ?Municipality $municipality = null
    ): array {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('municipalities', 'code')->ignore($municipality),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
