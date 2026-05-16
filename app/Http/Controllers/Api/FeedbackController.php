<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\Models\ServiceRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Feedback::class);

        $query = Feedback::query()
            ->with(['user', 'serviceRequest.service'])
            ->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        return $this->successResponse(
            FeedbackResource::collection($query->get()),
            'Feedback retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Feedback::class);

        $validated = $request->validate([
            'service_request_id' => ['required', 'exists:service_requests,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $serviceRequest = ServiceRequest::findOrFail($validated['service_request_id']);

        if ($serviceRequest->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized service request.', null, 403);
        }

        if ($serviceRequest->status !== 'completed') {
            return $this->errorResponse('Feedback can only be submitted for completed requests.', null, 422);
        }

        $existing = Feedback::where('service_request_id', $serviceRequest->id)->first();

        if ($existing) {
            return $this->errorResponse('Feedback already exists for this request.', null, 422);
        }

        $feedback = Feedback::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        $feedback->load(['user', 'serviceRequest.service']);

        return $this->successResponse(
            new FeedbackResource($feedback),
            'Feedback submitted successfully.',
            201
        );
    }

    public function show(Feedback $feedback)
    {
        $this->authorize('view', $feedback);

        $feedback->load(['user', 'serviceRequest.service']);

        return $this->successResponse(
            new FeedbackResource($feedback),
            'Feedback retrieved successfully.'
        );
    }

    public function destroy(Feedback $feedback)
    {
        $this->authorize('delete', $feedback);

        $feedback->delete();

        return $this->successResponse(null, 'Feedback deleted successfully.');
    }
}
