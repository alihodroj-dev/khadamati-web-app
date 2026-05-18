<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeedbackResponseResource;
use App\Models\Feedback;
use App\Models\FeedbackResponse;
use App\Notifications\FeedbackResponseNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackResponseController extends Controller
{
    use ApiResponse;

    public function store(Request $request, Feedback $feedback)
    {
        $this->authorize('respond', $feedback);

        $validated = $request->validate([
            'visibility' => ['required', Rule::in([
                FeedbackResponse::VISIBILITY_PUBLIC,
                FeedbackResponse::VISIBILITY_PRIVATE,
            ])],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $response = FeedbackResponse::query()->create([
            'feedback_id' => $feedback->id,
            'responder_id' => $request->user()->id,
            'visibility' => $validated['visibility'],
            'message' => $validated['message'],
        ]);

        $response->load('responder');

        if ($response->isPublic()) {
            $feedback->loadMissing('user');
            $feedback->user?->notify(new FeedbackResponseNotification($response));
        }

        return $this->successResponse(
            new FeedbackResponseResource($response),
            'Feedback response created successfully.',
            201
        );
    }

    public function update(Request $request, FeedbackResponse $feedbackResponse)
    {
        $this->authorize('update', $feedbackResponse);

        $validated = $request->validate([
            'visibility' => ['sometimes', Rule::in([
                FeedbackResponse::VISIBILITY_PUBLIC,
                FeedbackResponse::VISIBILITY_PRIVATE,
            ])],
            'message' => ['sometimes', 'required', 'string', 'max:5000'],
        ]);

        $feedbackResponse->update($validated);
        $feedbackResponse->load('responder');

        return $this->successResponse(
            new FeedbackResponseResource($feedbackResponse),
            'Feedback response updated successfully.'
        );
    }

    public function destroy(FeedbackResponse $feedbackResponse)
    {
        $this->authorize('delete', $feedbackResponse);

        $feedbackResponse->delete();

        return $this->successResponse(null, 'Feedback response deleted successfully.');
    }
}
