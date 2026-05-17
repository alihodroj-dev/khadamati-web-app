<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\Models\ServiceRequest;
use App\Support\StaffOfficeScope;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Feedback::class);

        $user = $request->user();

        $query = Feedback::query()
            ->with($this->feedbackEagerLoads($user))
            ->latest();

        if ($user->isAdmin()) {
            // Admins see all feedback.
        } elseif ($user->isStaff()) {
            StaffOfficeScope::applyFeedbackScope($query, $user);
        } else {
            $query->where('user_id', $user->id);
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

        $feedback->load($this->feedbackEagerLoads($request->user()));

        return $this->successResponse(
            new FeedbackResource($feedback),
            'Feedback submitted successfully.',
            201
        );
    }

    public function show(Feedback $feedback)
    {
        $this->authorize('view', $feedback);

        $feedback->load($this->feedbackEagerLoads(request()->user()));

        return $this->successResponse(
            new FeedbackResource($feedback),
            'Feedback retrieved successfully.'
        );
    }

    public function update(Request $request, Feedback $feedback)
    {
        $this->authorize('update', $feedback);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $feedback->update($validated);

        $feedback->load($this->feedbackEagerLoads($request->user()));

        return $this->successResponse(
            new FeedbackResource($feedback),
            'Feedback updated successfully.'
        );
    }

    public function destroy(Feedback $feedback)
    {
        $this->authorize('delete', $feedback);

        $feedback->delete();

        return $this->successResponse(null, 'Feedback deleted successfully.');
    }

    /**
     * @return array<int|string, mixed>
     */
    private function feedbackEagerLoads($user): array
    {
        return [
            'user',
            'serviceRequest.service',
            'responses' => function ($query) use ($user) {
                if (! $user->isAdmin() && ! $user->isStaff()) {
                    $query->public();
                }

                $query->with('responder');
            },
        ];
    }
}
