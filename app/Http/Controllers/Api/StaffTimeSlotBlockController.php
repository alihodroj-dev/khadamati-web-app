<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeTimeSlotBlockResource;
use App\Models\OfficeTimeSlotBlock;
use App\Support\OfficeTimeSlotValidation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class StaffTimeSlotBlockController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', OfficeTimeSlotBlock::class);

        $validated = $request->validate([
            'date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $query = OfficeTimeSlotBlock::query()
            ->with('staff')
            ->where('office_id', $request->user()->office_id)
            ->orderBy('date')
            ->orderBy('start_time');

        if (! empty($validated['date'])) {
            $query->whereDate('date', $validated['date']);
        }

        if (! empty($validated['staff_id'])) {
            $query->where('staff_id', $validated['staff_id']);
        }

        return $this->successResponse(
            OfficeTimeSlotBlockResource::collection($query->get()),
            'Time slot blocks retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', OfficeTimeSlotBlock::class);

        $user = $request->user();
        $validated = $request->validate(OfficeTimeSlotValidation::blockRules());
        OfficeTimeSlotValidation::assertStaffBelongsToOffice(
            isset($validated['staff_id']) ? (int) $validated['staff_id'] : null,
            (int) $user->office_id
        );

        $block = OfficeTimeSlotBlock::query()->create([
            ...OfficeTimeSlotValidation::normalizeBlockPayload($validated),
            'office_id' => $user->office_id,
        ]);

        $block->load('staff');

        return $this->successResponse(
            new OfficeTimeSlotBlockResource($block),
            'Time slot block created successfully.',
            201
        );
    }

    public function destroy(OfficeTimeSlotBlock $officeTimeSlotBlock)
    {
        $this->authorize('delete', $officeTimeSlotBlock);

        $officeTimeSlotBlock->delete();

        return $this->successResponse(null, 'Time slot block deleted successfully.');
    }
}
