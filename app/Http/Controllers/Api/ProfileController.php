<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        return $this->successResponse(
            new UserResource($request->user()),
            'Profile retrieved successfully.'
        );
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string'],
            'national_id' => [
                'nullable',
                'string',
                Rule::unique('users', 'national_id')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return $this->successResponse(
            new UserResource($user->fresh()),
            'Profile updated successfully.'
        );
    }
}
