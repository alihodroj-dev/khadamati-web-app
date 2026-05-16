<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request as HttpRequest;

class ServiceRequestController extends Controller
{
    /**
     * LIST ALL REQUESTS
     */
    public function index(HttpRequest $request)
    {
        $query = ServiceRequest::with(['user', 'service', 'staff']);

        // FILTER: status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(10);

        return view('admin.requests.index', compact('requests'));
    }

    /**
     * SHOW SINGLE REQUEST
     */
    public function show($id)
    {
        $request = ServiceRequest::with(['user', 'service', 'staff'])
            ->findOrFail($id);

        return view('admin.requests.show', compact('request'));
    }

    /**
     * UPDATE STATUS (ADMIN ACTION)
     */
    public function updateStatus(HttpRequest $httpRequest, $id)
    {
        $httpRequest->validate([
            'status' => 'required',
        ]);

        $request = ServiceRequest::findOrFail($id);

        $status = $httpRequest->status;

        // prevent invalid transitions
        $allowed = ['approved', 'rejected', 'completed', 'in_progress'];

        if (!in_array($status, $allowed)) {
            return back()->with('error', 'Invalid status');
        }

        $request->status = $status;

        if ($status === 'approved') {
            $request->reviewed_at = now();
        }

        if ($status === 'completed') {
            $request->completed_at = now();
        }

        if ($status === 'rejected') {
            $request->rejection_reason = $httpRequest->rejection_reason;
            $request->reviewed_at = now();
        }

        $request->save();

        return back()->with('success', 'Status updated');
    }

    /**
     * ASSIGN STAFF
     */
    public function assignStaff(HttpRequest $httpRequest, $id)
    {
        $httpRequest->validate([
            'staff_id' => 'required|exists:users,id',
        ]);

        $request = ServiceRequest::findOrFail($id);

        $request->assigned_staff_id = $httpRequest->staff_id;
        $request->status = 'under_review';

        $request->save();

        return back()->with('success', 'Staff assigned successfully');
    }

    /**
     * DELETE (optional admin cleanup)
     */
    public function destroy($id)
    {
        $request = ServiceRequest::findOrFail($id);
        $request->delete();

        return redirect()->route('admin.requests.index')
            ->with('success', 'Request deleted successfully');
    }
}
