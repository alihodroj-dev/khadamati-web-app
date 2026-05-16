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
    public function authenticate(array $payload): User
    {
        return DB::transaction(function () use ($payload) {
            $providerUserId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'] ?? $email;
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
