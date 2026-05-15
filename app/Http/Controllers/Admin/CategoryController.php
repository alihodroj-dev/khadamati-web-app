<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index');
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        return view('categories.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function show($id)
    {
        return view('categories.show', compact('id'));
    }
}