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

    public function edit($id)
    {
        return view('services.edit', compact('id'));
    }
}