<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'in:ios,android,web'],
        ]);

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'token' => $validated['token'],
            ],
            [
                'platform' => $validated['platform'],
                'last_used_at' => now(),
            ]
        );

        return $this->successResponse(
            [
                'id' => $deviceToken->id,
                'platform' => $deviceToken->platform,
                'last_used_at' => $deviceToken->last_used_at?->toISOString(),
            ],
            'Device token registered successfully.',
            $deviceToken->wasRecentlyCreated ? 201 : 200
        );
    }

    public function destroy(Request $request, DeviceToken $deviceToken)
    {
        if ($deviceToken->user_id !== $request->user()->id) {
            return $this->errorResponse(
                'You are not allowed to delete this device token.',
                null,
                403
            );
        }

        $deviceToken->delete();

        return $this->successResponse(
            null,
            'Device token removed successfully.'
        );
    }
}
