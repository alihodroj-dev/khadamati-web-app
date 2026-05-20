<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Support\AdminFormInput;
use App\Support\OfficeValidation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StaffOfficeController extends Controller
{
    public function edit()
    {
        $office = $this->resolveAssignedOffice();

        return view('staff.office.edit', compact('office'));
    }

    public function update(Request $request)
    {
        $office = $this->resolveAssignedOffice();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'image_url' => ['nullable', 'string', 'url', 'max:2048'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'working_hours' => ['nullable'],
        ]);

        $workingHours = AdminFormInput::parseWorkingHours($validated['working_hours'] ?? null);

        if (AdminFormInput::workingHoursInputHasValue($validated['working_hours'] ?? null) && $workingHours === null) {
            throw ValidationException::withMessages([
                'working_hours' => ['Select valid opening and closing times for each open day.'],
            ]);
        }

        unset($validated['working_hours']);

        $payload = OfficeValidation::onlyStaffFillable($validated);
        $payload['working_hours'] = $workingHours;

        $office->update($payload);

        return redirect()
            ->route('staff.office.edit')
            ->with('success', 'Office profile updated successfully.');
    }

    private function resolveAssignedOffice(): Office
    {
        $user = auth()->user();

        if ($user->office_id === null) {
            abort(403, 'No office assigned to this staff account.');
        }

        return Office::query()
            ->with('municipality')
            ->findOrFail($user->office_id);
    }
}
