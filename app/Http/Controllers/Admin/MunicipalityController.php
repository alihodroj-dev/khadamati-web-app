<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MunicipalityController extends Controller
{
    public function index()
    {
        $municipalities = Municipality::withCount('offices')->latest()->paginate(10);
        return view('admin.municipalities.index', compact('municipalities'));
    }

    public function create()
    {
        return view('admin.municipalities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:municipalities'],
            'code' => ['required', 'string', 'max:50', 'unique:municipalities'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        Municipality::create($validated);

        return redirect()->route('admin.municipalities.index')
            ->with('success', 'Municipality created successfully.');
    }

    public function edit(Municipality $municipality)
    {
        return view('admin.municipalities.edit', compact('municipality'));
    }

    public function update(Request $request, Municipality $municipality)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('municipalities')->ignore($municipality->id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('municipalities')->ignore($municipality->id)],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $municipality->update($validated);

        return redirect()->route('admin.municipalities.index')
            ->with('success', 'Municipality updated successfully.');
    }

    public function destroy(Municipality $municipality)
    {
        if ($municipality->offices()->exists()) {
            return back()->with('error', 'Cannot delete municipality with existing offices.');
        }

        $municipality->delete();
        return back()->with('success', 'Municipality deleted successfully.');
    }

    public function show(Municipality $municipality)
    {
        $municipality->load(['offices' => function ($query) {
            $query->withCount('serviceRequests');
        }]);
        
        return view('admin.municipalities.show', compact('municipality'));
    }
}