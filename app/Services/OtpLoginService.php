<?php

namespace App\Services;

use App\Mail\LoginOtpMail;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OtpLoginService
{
    private const OTP_LENGTH = 6;

    public const EXPIRY_MINUTES = 5;

    private const MAX_ATTEMPTS = 5;

    /**
     * @return array{
     *     requires_otp: true,
     *     challenge_token: string,
     *     expires_at: string
     * }
     */
    public function requestOtp(string $email): array
    {
        $user = $this->resolveUserForOtp($email);

        return $this->createAndSendOtpChallenge($user);
    }

    /**
     * @param  list<string>  $allowedRoles
     * @return array{
     *     requires_otp: true,
     *     challenge_token: string,
     *     expires_at: string
     * }
     */
    public function requestOtpForExistingUser(string $email, array $allowedRoles): array
    {
        $user = User::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['We could not find an account with that email address.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive.'],
            ]);
        }

        if (! in_array($user->role, $allowedRoles, true)) {
            throw ValidationException::withMessages([
                'email' => ['This account cannot use OTP sign-in.'],
            ]);
        }

        return $this->createAndSendOtpChallenge($user);
    }

    /**
     * @deprecated Use requestOtp() for passwordless email login.
     */
    public function initiateLogin(string $email, string $password): array
    {
        return $this->requestOtp($email);
    }

    public function verifyOtp(string $challengeToken, string $otp): User
    {
        $challenge = $this->findUsableChallenge($challengeToken);

        if ($challenge->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages([
                'otp' => ['Too many invalid attempts. Please request a new code.'],
            ]);
        }

        if (! Hash::check($otp, $challenge->otp_hash)) {
            $challenge->increment('attempts');

            throw ValidationException::withMessages([
                'otp' => ['The verification code is invalid.'],
            ]);
        }

        $challenge->update(['consumed_at' => now()]);

        $user = $challenge->user()->first();

        if (! $user || ! $user->is_active) {
            throw ValidationException::withMessages([
                'challenge_token' => ['This login challenge is no longer valid.'],
            ]);
        }

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()]);
            $user->save();
        }

        return $user->fresh();
    }

    protected function resolveUserForOtp(string $email): User
    {
        $email = strtolower(trim($email));

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            if (! $user->is_active) {
                throw ValidationException::withMessages([
                    'email' => ['Your account is inactive.'],
                ]);
            }

            if (! $user->isCitizen()) {
                throw ValidationException::withMessages([
                    'email' => ['This email cannot be used for citizen sign-in.'],
                ]);
            }

            return $user;
        }

        return User::create([
            'name' => $this->placeholderNameFromEmail($email),
            'email' => $email,
            'password' => null,
            'role' => User::ROLE_CITIZEN,
            'is_active' => true,
        ]);
    }

    protected function placeholderNameFromEmail(string $email): string
    {
        $localPart = strstr($email, '@', true);

        if (is_string($localPart) && $localPart !== '') {
            return $localPart;
        }

        return 'Citizen';
    }

    /**
     * @return array{
     *     challenge_token: string,
     *     expires_at: string,
     *     message: string,
     *     development_message?: string
     * }
     */
    public function resendOtp(string $challengeToken): array
    {
        $challenge = OtpChallenge::query()
            ->where('challenge_token', $challengeToken)
            ->first();

        if (! $challenge || $challenge->isConsumed()) {
            throw ValidationException::withMessages([
                'challenge_token' => ['The login challenge is invalid.'],
            ]);
        }

        $user = $challenge->user;

        if (! $user || ! $user->is_active) {
            throw ValidationException::withMessages([
                'challenge_token' => ['The login challenge is no longer valid.'],
            ]);
        }

        $otp = $this->generateOtp();

        $challenge->update([
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'attempts' => 0,
        ]);

        $this->deliverOtp($user, $otp);

        $response = [
            'challenge_token' => $challenge->challenge_token,
            'expires_at' => $challenge->expires_at->toISOString(),
            'message' => 'A new verification code has been sent.',
        ];

        if (app()->environment('local')) {
            $response['development_message'] = 'For local development, check the Laravel application log for the OTP code.';
        }

        return $response;
    }

    protected function findUsableChallenge(string $challengeToken): OtpChallenge
    {
        $challenge = OtpChallenge::query()
            ->where('challenge_token', $challengeToken)
            ->first();

        if (! $challenge || $challenge->isConsumed()) {
            throw ValidationException::withMessages([
                'challenge_token' => ['The login challenge is invalid.'],
            ]);
        }

        if ($challenge->isExpired()) {
            throw ValidationException::withMessages([
                'otp' => ['The verification code has expired. Please sign in again.'],
            ]);
        }

        return $challenge;
    }

    protected function invalidatePendingChallenges(User $user): void
    {
        OtpChallenge::query()
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);
    }

    protected function generateOtp(): string
    {
        return str_pad((string) random_int(0, 10 ** self::OTP_LENGTH - 1), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    protected function deliverOtp(User $user, string $otp): void
    {
        Mail::to($user->email)->send(new LoginOtpMail($otp, $user, self::EXPIRY_MINUTES));

        Log::info('Login OTP generated.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'channel' => OtpChallenge::CHANNEL_EMAIL,
            'otp' => $otp,
        ]);
    }

    /**
     * @return array{
     *     requires_otp: true,
     *     challenge_token: string,
     *     expires_at: string
     * }
     */
    protected function createAndSendOtpChallenge(User $user): array
    {
        $this->invalidatePendingChallenges($user);

        $otp = $this->generateOtp();
        $challenge = OtpChallenge::create([
            'challenge_token' => Str::random(64),
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'channel' => OtpChallenge::CHANNEL_EMAIL,
        ]);

        $this->deliverOtp($user, $otp);

        return [
            'requires_otp' => true,
            'challenge_token' => $challenge->challenge_token,
            'expires_at' => $challenge->expires_at->toISOString(),
        ];
    }
}
