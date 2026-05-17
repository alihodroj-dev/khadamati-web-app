<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\Support\StaffOfficeScope;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class StaffFeedbackController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Feedback::class);

        $user = $request->user();

        $query = Feedback::query()
            ->with([
                'user',
                'serviceRequest.service',
                'serviceRequest.office',
                'responses.responder',
            ])
            ->latest();

        StaffOfficeScope::applyFeedbackScope($query, $user);

        return $this->successResponse(
            FeedbackResource::collection($query->get()),
            'Office feedback retrieved successfully.'
        );
    }
}
