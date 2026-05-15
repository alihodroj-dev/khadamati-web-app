<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return view('services.index');
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit($id)
    {
        return view('services.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()
            ->route('services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy($id)
    {
        return redirect()
            ->route('services.index')
            ->with('success', 'Service deleted successfully.');
    }

    public function show($id)
    {
        return view('services.show', compact('id'));
    }
}