<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\User;
use App\Support\OfficeValidation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class StaffOfficeController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        $office = $this->resolveAssignedOffice($request->user());
        $office->load('municipality');

        return $this->successResponse(
            new OfficeResource($office),
            'Office profile retrieved successfully.'
        );
    }

    public function update(Request $request)
    {
        $office = $this->resolveAssignedOffice($request->user());

        $this->authorize('update', $office);

        $validated = OfficeValidation::validateStaffUpdate($request);

        $office->update(OfficeValidation::onlyStaffFillable($validated));
        $office->load('municipality');

        return $this->successResponse(
            new OfficeResource($office),
            'Office profile updated successfully.'
        );
    }

    private function resolveAssignedOffice(User $user): Office
    {
        if ($user->office_id === null) {
            abort(403, 'No office assigned to this staff account.');
        }

        return Office::query()->findOrFail($user->office_id);
    }
}
