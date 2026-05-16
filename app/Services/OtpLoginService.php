<?php

namespace App\Services;

use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OtpLoginService
{
    private const OTP_LENGTH = 6;

    private const EXPIRY_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    /**
     * @return array{
     *     requires_otp: true,
     *     challenge_token: string,
     *     expires_at: string
     * }
     */
    public function initiateLogin(string $email, string $password): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive.'],
            ]);
        }

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

        return $user;
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
        Log::info('Login OTP generated.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'channel' => OtpChallenge::CHANNEL_EMAIL,
            'otp' => $otp,
        ]);
    }
}
