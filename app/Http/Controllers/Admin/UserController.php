<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        return view('users.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function show($id)
    {
        return view('users.show', compact('id'));
    }
}