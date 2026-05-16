<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AppleAuthenticationService
{
    private const PRIVATE_RELAY_DOMAIN = 'privaterelay.appleid.com';

    /**
     * @param  array{
     *     sub: string,
     *     email?: string
     * }  $payload
     */
    public function authenticate(array $payload, ?string $fullName = null): User
    {
        return DB::transaction(function () use ($payload, $fullName) {
            $providerUserId = $payload['sub'];
            $email = $this->normalizeEmail($payload['email'] ?? null);

            $socialAccount = SocialAccount::query()
                ->where('provider', SocialAccount::PROVIDER_APPLE)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($socialAccount) {
                if ($email !== null) {
                    $socialAccount->update(['email' => $email]);
                }

                return $socialAccount->user;
            }

            $user = $email !== null
                ? User::query()->where('email', $email)->first()
                : null;

            if (! $user) {
                if ($email === null) {
                    throw new InvalidArgumentException(
                        'Email is required for the first Apple Sign-In. Authorize email sharing and try again.'
                    );
                }

                $user = User::create([
                    'name' => $this->resolveNameForNewUser($fullName, $email),
                    'email' => $email,
                    'password' => null,
                    'role' => User::ROLE_CITIZEN,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }

            SocialAccount::query()->updateOrCreate(
                [
                    'provider' => SocialAccount::PROVIDER_APPLE,
                    'provider_user_id' => $providerUserId,
                ],
                [
                    'user_id' => $user->id,
                    'email' => $email ?? $user->email,
                    'avatar_url' => null,
                ]
            );

            return $user;
        });
    }

    protected function normalizeEmail(mixed $email): ?string
    {
        if (! is_string($email)) {
            return null;
        }

        $email = strtolower(trim($email));

        return $email === '' ? null : $email;
    }

    protected function resolveNameForNewUser(?string $fullName, string $email): string
    {
        $fullName = is_string($fullName) ? trim($fullName) : '';

        if ($fullName !== '') {
            return $fullName;
        }

        $localPart = strstr($email, '@', true);

        if (is_string($localPart) && $localPart !== '') {
            return $localPart;
        }

        return 'Apple User';
    }

    public static function isPrivateRelayEmail(string $email): bool
    {
        return str_ends_with(strtolower($email), '@'.self::PRIVATE_RELAY_DOMAIN);
    }

    /**
     * @param  array{
     *     sub: string,
     *     email?: string
     * }  $payload
     */
    public function linkSocialAccount(User $user, array $payload): SocialAccount
    {
        $email = $this->normalizeEmail($payload['email'] ?? null) ?? $user->email;

        return SocialAccount::query()->updateOrCreate(
            [
                'provider' => SocialAccount::PROVIDER_APPLE,
                'provider_user_id' => $payload['sub'],
            ],
            [
                'user_id' => $user->id,
                'email' => $email,
                'avatar_url' => null,
            ]
        );
    }
}
