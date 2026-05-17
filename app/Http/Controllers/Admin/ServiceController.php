<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\AdminFormInput;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('category')->latest()->paginate(10);

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        $categories = ServiceCategory::query()
            ->orderBy('name')
            ->get();

        return view('admin.services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        Service::query()->create($this->validatedServiceData($request));

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        $categories = ServiceCategory::query()->orderBy('name')->get();

        return view('admin.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $service->update($this->validatedServiceData($request));

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    public function show($id)
    {
        $service = Service::with('category')->findOrFail($id);

        return view('admin.services.show', compact('service'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedServiceData(Request $request): array
    {
        $validated = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_fee' => ['required', 'numeric', 'min:0'],
            'estimated_processing_days' => ['nullable', 'integer', 'min:0'],
            'required_documents' => ['nullable', 'string'],
            'requires_appointment' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
            'is_active' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
        ]);

        return [
            'service_category_id' => (int) $validated['service_category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_fee' => $validated['base_fee'],
            'estimated_processing_days' => $validated['estimated_processing_days'] ?? null,
            'required_documents' => AdminFormInput::parseRequiredDocuments(
                $validated['required_documents'] ?? null
            ),
            'requires_appointment' => AdminFormInput::boolean($validated['requires_appointment']),
            'is_active' => AdminFormInput::boolean($validated['is_active']),
        ];
    }
}
