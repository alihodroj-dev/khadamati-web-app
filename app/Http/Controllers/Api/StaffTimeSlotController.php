<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeTimeSlotResource;
use App\Models\OfficeTimeSlot;
use App\Support\OfficeTimeSlotValidation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class StaffTimeSlotController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', OfficeTimeSlot::class);

        $user = $request->user();

        $query = OfficeTimeSlot::query()
            ->with('staff')
            ->where('office_id', $user->office_id)
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->integer('staff_id'));
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->integer('day_of_week'));
        }

        return $this->successResponse(
            OfficeTimeSlotResource::collection($query->get()),
            'Office time slots retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', OfficeTimeSlot::class);

        $user = $request->user();
        $validated = $request->validate(OfficeTimeSlotValidation::slotRules());
        OfficeTimeSlotValidation::assertStaffBelongsToOffice(
            isset($validated['staff_id']) ? (int) $validated['staff_id'] : null,
            (int) $user->office_id
        );

        $slot = OfficeTimeSlot::query()->create([
            ...OfficeTimeSlotValidation::normalizeSlotPayload($validated),
            'office_id' => $user->office_id,
            'slot_duration_minutes' => $validated['slot_duration_minutes'] ?? 30,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $slot->load('staff');

        return $this->successResponse(
            new OfficeTimeSlotResource($slot),
            'Office time slot created successfully.',
            201
        );
    }

    public function update(Request $request, OfficeTimeSlot $officeTimeSlot)
    {
        $this->authorize('update', $officeTimeSlot);

        $validated = $request->validate(OfficeTimeSlotValidation::slotRules(partial: true));

        if (array_key_exists('staff_id', $validated)) {
            OfficeTimeSlotValidation::assertStaffBelongsToOffice(
                isset($validated['staff_id']) ? (int) $validated['staff_id'] : null,
                (int) $officeTimeSlot->office_id
            );
        }

        $officeTimeSlot->update(OfficeTimeSlotValidation::normalizeSlotPayload($validated));
        $officeTimeSlot->load('staff');

        return $this->successResponse(
            new OfficeTimeSlotResource($officeTimeSlot),
            'Office time slot updated successfully.'
        );
    }

    public function destroy(OfficeTimeSlot $officeTimeSlot)
    {
        $this->authorize('delete', $officeTimeSlot);

        $officeTimeSlot->delete();

        return $this->successResponse(null, 'Office time slot deleted successfully.');
    }
}
