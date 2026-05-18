<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\ProfileCompletionService;
use App\Support\UserProfileCompletion;
use App\Traits\ApiResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->errorResponse(
                'The current password is incorrect.',
                ['current_password' => ['The current password is incorrect.']],
                422
            );
        }

        $user->update(['password' => $validated['password']]);

        $currentTokenId = $request->user()->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return $this->successResponse(null, 'Password updated successfully.');
    }

    public function complete(
        Request $request,
        ProfileCompletionService $profileCompletionService
    ) {
        $user = $request->user();

        if (! $user->isCitizen()) {
            return $this->errorResponse(
                'Only citizens can complete a profile.',
                null,
                403
            );
        }

        $validated = $request->validate([
            'verification_session_token' => ['required', 'string'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'national_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'national_id')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $user = $profileCompletionService->complete($user, $validated);
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Profile could not be completed.',
                $e->errors(),
                422
            );
        }

        $user = UserProfileCompletion::sync($user);

        return $this->successResponse(
            new UserResource($user),
            'Profile completed successfully.'
        );
    }

    public function updateNotificationPreferences(Request $request)
    {
        // DEFERRED(roadmap): Staff/admin reminder preferences when email/SMS reminders ship.
        // See docs/admin-office-roadmap.md#email--sms-appointment-reminders
        $user = $request->user();

        if (! $user->isCitizen()) {
            return $this->errorResponse(
                'Only citizens can update notification preferences.',
                null,
                403
            );
        }

        $validated = $request->validate([
            'push_notifications_enabled' => ['sometimes', 'boolean'],
            'email_notifications_enabled' => ['sometimes', 'boolean'],
            'sms_notifications_enabled' => ['sometimes', 'boolean'],
        ]);

        $user->update($validated);

        return $this->successResponse(
            new UserResource($user->fresh()),
            'Notification preferences updated successfully.'
        );
    }
}
