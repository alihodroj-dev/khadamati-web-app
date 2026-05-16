<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use App\Models\ServiceRequest;

class StaffRequestController extends Controller
{
    /**
     * Show only assigned requests
     */
    public function index()
    {
        $requests = ServiceRequest::with(['service', 'user'])
            ->where('assigned_staff_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('staff.requests.index', compact('requests'));
    }

    /**
     * Show single request
     */
    public function show($id)
    {
        $request = ServiceRequest::with(['service', 'user'])
            ->where('assigned_staff_id', auth()->id())
            ->findOrFail($id);

        return view('staff.requests.show', compact('request'));
    }

    /**
     * Update status + staff notes
     */
    public function updateStatus(HttpRequest $httpRequest, $id)
    {
        $request = ServiceRequest::where('assigned_staff_id', auth()->id())
            ->findOrFail($id);

        $request->status = $httpRequest->status;
        $request->staff_notes = $httpRequest->staff_notes;

        if ($httpRequest->status === 'completed') {
            $request->completed_at = now();
        }

        $request->save();

        return back()->with('success', 'Request updated successfully');
    }
}
