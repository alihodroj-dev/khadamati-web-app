<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AppleAuthenticationService;
use App\Services\AppleIdentityTokenVerificationService;
use App\Services\GoogleAuthenticationService;
use App\Services\GoogleIdTokenVerificationService;
use App\Services\OtpLoginService;
use App\Services\RegistrationCompletionService;
use App\Support\UserProfileCompletion;
use App\Traits\ApiResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;

class AuthController extends Controller
{
    use ApiResponse;

    public function completeRegistration(
        Request $request,
        RegistrationCompletionService $registrationCompletionService
    ) {
        $validated = $request->validate([
            'verification_session_token' => ['required', 'string'],
            'auth_provider' => ['required', 'string', Rule::in(['google', 'apple', 'email'])],
            'provider_token' => ['nullable', 'string', 'required_if:auth_provider,google,apple'],
            'email' => ['required', 'email', 'unique:users,email'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'national_id' => ['required', 'string', 'max:255', 'unique:users,national_id'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'required_if:auth_provider,email'],
        ]);

        try {
            $user = $registrationCompletionService->complete($validated);
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Registration could not be completed.',
                $e->errors(),
                422
            );
        }

        $token = $user->createToken('khadamati-ios-app')->plainTextToken;

        return $this->successResponse(
            $this->authenticationPayload($user, $token),
            'Registered successfully',
            201
        );
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string'],
            'national_id' => ['nullable', 'string', 'unique:users,national_id'],
            'id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'national_id' => $validated['national_id'] ?? null,
            'role' => User::ROLE_CITIZEN,
        ]);

        if ($request->hasFile('id_document')) {
            $path = $request->file('id_document')->store(
                'id-documents/' . $user->id,
                'public'
            );

            $user->update(['id_document_path' => $path]);
        }

        $token = $user->createToken('khadamati-ios-app')->plainTextToken;

        return $this->successResponse(
            [
                'user' => new UserResource($user->fresh()),
                'token' => $token,
            ],
            'Registered successfully',
            201
        );
    }

    public function google(
        Request $request,
        GoogleIdTokenVerificationService $tokenVerifier,
        GoogleAuthenticationService $googleAuth
    ) {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        try {
            $payload = $tokenVerifier->verify($validated['id_token']);
            $user = $googleAuth->authenticate($payload)->fresh();
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse(
                'Invalid Google ID token.',
                ['id_token' => [$e->getMessage()]],
                401
            );
        } catch (RuntimeException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                null,
                500
            );
        }

        if (! $user->is_active) {
            return $this->errorResponse(
                'Your account is inactive.',
                null,
                403
            );
        }

        $token = $user->createToken('khadamati-ios-app')->plainTextToken;

        return $this->successResponse(
            $this->authenticationPayload($user, $token),
            'Logged in successfully'
        );
    }

    public function apple(
        Request $request,
        AppleIdentityTokenVerificationService $tokenVerifier,
        AppleAuthenticationService $appleAuth
    ) {
        $validated = $request->validate([
            'identity_token' => ['required', 'string'],
            'full_name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $payload = $tokenVerifier->verify($validated['identity_token']);
            $user = $appleAuth->authenticate(
                $payload,
                $validated['full_name'] ?? null
            )->fresh();
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse(
                'Invalid Apple identity token.',
                ['identity_token' => [$e->getMessage()]],
                401
            );
        } catch (RuntimeException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                null,
                500
            );
        }

        if (! $user->is_active) {
            return $this->errorResponse(
                'Your account is inactive.',
                null,
                403
            );
        }

        $token = $user->createToken('khadamati-ios-app')->plainTextToken;

        return $this->successResponse(
            $this->authenticationPayload($user, $token),
            'Logged in successfully'
        );
    }

    public function requestOtp(Request $request, OtpLoginService $otpLoginService)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $challenge = $otpLoginService->requestOtp($validated['email']);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Invalid credentials.';

            return $this->errorResponse(
                $message,
                $e->errors(),
                str_contains($message, 'inactive') ? 403 : 422
            );
        }

        $responseMessage = 'Verification code sent.';

        if (app()->environment('local')) {
            $responseMessage = 'Verification code sent. For local development, check the Laravel application log for the OTP code.';
        }

        return $this->successResponse(
            $challenge,
            $responseMessage
        );
    }

    public function verifyOtp(Request $request, OtpLoginService $otpLoginService)
    {
        $validated = $request->validate([
            'challenge_token' => ['required', 'string'],
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        try {
            $user = $otpLoginService->verifyOtp(
                $validated['challenge_token'],
                $validated['otp']
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'OTP verification failed.',
                $e->errors(),
                422
            );
        }

        $token = $user->createToken('khadamati-ios-app')->plainTextToken;

        return $this->successResponse(
            $this->authenticationPayload($user->fresh(), $token),
            'Logged in successfully'
        );
    }

    public function login(Request $request, OtpLoginService $otpLoginService)
    {
        return $this->requestOtp($request, $otpLoginService);
    }

    public function resendOtp(Request $request, OtpLoginService $otpLoginService)
    {
        $validated = $request->validate([
            'challenge_token' => ['required', 'string'],
        ]);

        try {
            $result = $otpLoginService->resendOtp($validated['challenge_token']);
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Unable to resend verification code.',
                $e->errors(),
                422
            );
        }

        $message = $result['message'];
        unset($result['message']);

        if (isset($result['development_message'])) {
            $message = $result['development_message'];
            unset($result['development_message']);
        }

        return $this->successResponse($result, $message);
    }

    public function me(Request $request)
    {
        return $this->successResponse(
            [
                'user' => $request->user(),
            ],
            'User retrieved successfully'
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(
            null,
            'Logged out successfully'
        );
    }

    /**
     * @return array{
     *     user: UserResource,
     *     token: string,
     *     profile_completed: bool
     * }
     */
    protected function authenticationPayload(User $user, string $token): array
    {
        return [
            'user' => new UserResource($user),
            'token' => $token,
            'profile_completed' => UserProfileCompletion::isCompleted($user),
        ];
    }
}