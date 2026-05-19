<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GoogleAuthenticationService
{
    /**
     * @param  array{
     *     sub: string,
     *     email: string,
     *     name?: string,
     *     picture?: string
     * }  $payload
     */
    public function authenticate(
        array $payload,
        ?string $firstName = null,
        ?string $lastName = null,
    ): User {
        return DB::transaction(function () use ($payload, $firstName, $lastName) {
            $providerUserId = $payload['sub'];
            $email = $payload['email'];
            $name = $this->resolveDisplayName($firstName, $lastName, $payload['name'] ?? null, $email);
            $avatarUrl = $payload['picture'] ?? null;

            $socialAccount = SocialAccount::query()
                ->where('provider', SocialAccount::PROVIDER_GOOGLE)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($socialAccount) {
                $socialAccount->update([
                    'email' => $email,
                    'avatar_url' => $avatarUrl,
                ]);

                return $socialAccount->user;
            }

            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => null,
                    'role' => User::ROLE_CITIZEN,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }

            SocialAccount::query()->updateOrCreate(
                [
                    'provider' => SocialAccount::PROVIDER_GOOGLE,
                    'provider_user_id' => $providerUserId,
                ],
                [
                    'user_id' => $user->id,
                    'email' => $email,
                    'avatar_url' => $avatarUrl,
                ]
            );

            return $user;
        });
    }

    protected function resolveDisplayName(
        ?string $firstName,
        ?string $lastName,
        ?string $tokenName,
        string $email,
    ): string {
        $firstName = is_string($firstName) ? trim($firstName) : '';
        $lastName = is_string($lastName) ? trim($lastName) : '';

        if ($firstName !== '' || $lastName !== '') {
            return trim($firstName.' '.$lastName);
        }

        $tokenName = is_string($tokenName) ? trim($tokenName) : '';

        if ($tokenName !== '') {
            return $tokenName;
        }

        $localPart = strstr($email, '@', true);

        return is_string($localPart) && $localPart !== '' ? $localPart : 'Citizen';
    }

    /**
     * @param  array{
     *     sub: string,
     *     email: string,
     *     picture?: string
     * }  $payload
     */
    public function linkSocialAccount(User $user, array $payload): SocialAccount
    {
        return SocialAccount::query()->updateOrCreate(
            [
                'provider' => SocialAccount::PROVIDER_GOOGLE,
                'provider_user_id' => $payload['sub'],
            ],
            [
                'user_id' => $user->id,
                'email' => $payload['email'],
                'avatar_url' => $payload['picture'] ?? null,
            ]
        );
    }
}
