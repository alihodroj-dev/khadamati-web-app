<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Support\StaffOfficeScope;
use Illuminate\Http\Request;

class StaffFeedbackController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $feedbacks = Feedback::whereHas('serviceRequest', function($query) use ($user) {
            StaffOfficeScope::applyServiceRequestScope($query, $user);
        })
        ->with(['user', 'serviceRequest.service'])
        ->latest()
        ->paginate(20);

        return view('staff.feedback.index', compact('feedbacks'));
    }
}