<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackResponse;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with(['user', 'serviceRequest.service'])
            ->latest()
            ->paginate(20);

        return view('admin.feedback.index', compact('feedbacks'));
    }

    public function show($id)
    {
        $feedback = Feedback::with(['user', 'serviceRequest.service', 'responses.responder'])
            ->findOrFail($id);

        return view('admin.feedback.show', compact('feedback'));
    }

    public function respond(Request $request, $id)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'visibility' => ['required', 'in:public,private'],
        ]);

        $feedback = Feedback::findOrFail($id);

        $response = FeedbackResponse::create([
            'feedback_id' => $feedback->id,
            'responder_id' => auth()->id(),
            'visibility' => $request->visibility,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Response added successfully.');
    }
}