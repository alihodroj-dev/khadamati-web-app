<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Feedback;
use App\Models\FeedbackResponse;
use Illuminate\Http\Request;

class CitizenFeedbackController extends Controller
{
    public function create($requestId)
    {
        $serviceRequest = ServiceRequest::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->with('service')
            ->findOrFail($requestId);

        // Check if feedback already exists
        $existingFeedback = Feedback::where('service_request_id', $serviceRequest->id)->first();
        
        if ($existingFeedback) {
            return redirect()->route('citizen.feedback.show', $existingFeedback->id);
        }

        return view('citizen.feedback.create', compact('serviceRequest'));
    }

    public function store(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequest::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->findOrFail($requestId);

        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $feedback = Feedback::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('citizen.feedback.show', $feedback->id)
            ->with('success', 'Thank you for your feedback!');
    }

    public function show($id)
    {
        $feedback = Feedback::where('user_id', auth()->id())
            ->with(['serviceRequest.service', 'responses' => function ($query) {
                $query->latest();
            }])
            ->findOrFail($id);

        return view('citizen.feedback.show', compact('feedback'));
    }

    public function edit($id)
    {
        $feedback = Feedback::where('user_id', auth()->id())
            ->whereDoesntHave('responses')
            ->findOrFail($id);

        return view('citizen.feedback.edit', compact('feedback'));
    }

    public function update(Request $request, $id)
    {
        $feedback = Feedback::where('user_id', auth()->id())
            ->whereDoesntHave('responses')
            ->findOrFail($id);

        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $feedback->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('citizen.feedback.show', $feedback->id)
            ->with('success', 'Feedback updated successfully.');
    }
}