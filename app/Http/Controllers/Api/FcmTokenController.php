<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $request->user()->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return $this->successResponse(
            [
                'has_fcm_token' => true,
            ],
            'FCM token saved successfully.'
        );
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }

    public function destroy(Request $request)
    {
        $request->user()->update([
            'fcm_token' => null,
        ]);

        return $this->successResponse(
            [
                'has_fcm_token' => false,
            ],
            'FCM token removed successfully.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'fcm_token' => ['required', 'string', 'max:4096'],
        ];
    }
}
