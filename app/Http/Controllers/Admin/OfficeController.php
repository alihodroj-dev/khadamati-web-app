<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use App\Models\Office;
use App\Support\AdminFormInput;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::query()
            ->with('municipality')
            ->withCount('serviceRequests')
            ->latest()
            ->paginate(10);

        return view('admin.offices.index', compact('offices'));
    }

    public function create()
    {
        $municipalities = Municipality::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.offices.create', compact('municipalities'));
    }

    public function store(Request $request)
    {
        Office::query()->create($this->validatedOfficeData($request));

        return redirect()
            ->route('admin.offices.index')
            ->with('success', 'Office created successfully.');
    }

    public function edit($id)
    {
        $office = Office::findOrFail($id);
        $municipalities = Municipality::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.offices.edit', compact('office', 'municipalities'));
    }

    public function update(Request $request, $id)
    {
        $office = Office::findOrFail($id);
        $office->update($this->validatedOfficeData($request));

        return redirect()
            ->route('admin.offices.index')
            ->with('success', 'Office updated successfully.');
    }

    public function destroy($id)
    {
        $office = Office::findOrFail($id);

        if ($office->serviceRequests()->exists()) {
            return redirect()
                ->route('admin.offices.index')
                ->with('error', 'Cannot delete an office that has service requests. Deactivate it instead.');
        }

        $office->delete();

        return redirect()
            ->route('admin.offices.index')
            ->with('success', 'Office deleted successfully.');
    }

    public function show($id)
    {
        $office = Office::query()
            ->with('municipality')
            ->withCount('serviceRequests')
            ->findOrFail($id);

        return view('admin.offices.show', compact('office'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedOfficeData(Request $request): array
    {
        $validated = $request->validate([
            'municipality_id' => ['nullable', 'exists:municipalities,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'image_url' => ['nullable', 'string', 'url', 'max:2048'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'working_hours' => ['nullable'],
            'is_active' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
        ]);

        $workingHours = AdminFormInput::parseWorkingHours($validated['working_hours'] ?? null);

        if (AdminFormInput::workingHoursInputHasValue($validated['working_hours'] ?? null) && $workingHours === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'working_hours' => ['Select valid opening and closing times for each open day.'],
            ]);
        }

        return [
            'municipality_id' => ! empty($validated['municipality_id'])
                ? (int) $validated['municipality_id']
                : null,
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'working_hours' => $workingHours,
            'is_active' => AdminFormInput::boolean($validated['is_active']),
        ];
    }
}
