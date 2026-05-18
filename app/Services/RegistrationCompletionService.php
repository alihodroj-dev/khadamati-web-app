<?php

namespace App\Services;

use App\Models\IdentityVerificationSession;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\UserProfileCompletion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class RegistrationCompletionService
{
    public function __construct(
        private GoogleIdTokenVerificationService $googleTokenVerifier,
        private AppleIdentityTokenVerificationService $appleTokenVerifier,
        private GoogleAuthenticationService $googleAuth,
        private AppleAuthenticationService $appleAuth
    ) {}

    /**
     * @param  array{
     *     verification_session_token: string,
     *     auth_provider: string,
     *     provider_token?: string,
     *     email: string,
     *     first_name: string,
     *     last_name: string,
     *     father_name: string,
     *     mother_name: string,
     *     date_of_birth: string,
     *     national_id: string,
     *     phone?: string|null,
     *     password?: string
     * }  $data
     */
    public function complete(array $data): User
    {
        $session = IdentityVerificationSession::query()
            ->where('session_token', $data['verification_session_token'])
            ->first();

        if (! $session) {
            throw ValidationException::withMessages([
                'verification_session_token' => ['The identity verification session is invalid.'],
            ]);
        }

        if (! $session->isConsumable()) {
            throw ValidationException::withMessages([
                'verification_session_token' => ['The identity verification session has expired or was already used.'],
            ]);
        }

        return DB::transaction(function () use ($data, $session) {
            $email = strtolower(trim($data['email']));

            $userAttributes = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'father_name' => $data['father_name'],
                'mother_name' => $data['mother_name'],
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $email,
                'date_of_birth' => $data['date_of_birth'],
                'national_id' => $data['national_id'],
                'phone' => $data['phone'] ?? null,
                'role' => User::ROLE_CITIZEN,
                'is_active' => true,
                'email_verified_at' => now(),
            ];

            $providerPayload = match ($data['auth_provider']) {
                'google' => $this->resolveGoogleProvider($data['provider_token'] ?? '', $email, $userAttributes),
                'apple' => $this->resolveAppleProvider($data['provider_token'] ?? '', $email, $userAttributes),
                'email' => $this->resolveEmailProvider($data['password'] ?? '', $userAttributes),
                default => throw ValidationException::withMessages([
                    'auth_provider' => ['The selected authentication provider is invalid.'],
                ]),
            };

            $user = User::create($userAttributes);

            if (is_array($providerPayload)) {
                match ($data['auth_provider']) {
                    'google' => $this->googleAuth->linkSocialAccount($user, $providerPayload),
                    'apple' => $this->appleAuth->linkSocialAccount($user, $providerPayload),
                    default => null,
                };
            }

            [$idFrontPath, $idBackPath] = $this->moveIdentityDocumentsToUser($session, $user->id);

            $user->update([
                'id_front_path' => $idFrontPath,
                'id_back_path' => $idBackPath,
            ]);

            $session->update([
                'status' => IdentityVerificationSession::STATUS_CONSUMED,
            ]);

            return UserProfileCompletion::sync($user->fresh());
        });
    }

    /**
     * @param  array<string, mixed>  $userAttributes
     * @return array<string, mixed>
     */
    protected function resolveGoogleProvider(
        string $providerToken,
        string $email,
        array &$userAttributes
    ): array {
        if ($providerToken === '') {
            throw ValidationException::withMessages([
                'provider_token' => ['A Google ID token is required.'],
            ]);
        }

        try {
            $payload = $this->googleTokenVerifier->verify($providerToken);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'provider_token' => ['The Google ID token is invalid.'],
            ]);
        } catch (RuntimeException) {
            throw ValidationException::withMessages([
                'provider_token' => ['Google Sign-In is not available right now. Please try again later.'],
            ]);
        }

        $this->assertTokenEmailMatches($payload['email'] ?? '', $email, 'google');
        $this->assertSocialAccountAvailable(SocialAccount::PROVIDER_GOOGLE, $payload['sub']);

        $userAttributes['password'] = null;

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $userAttributes
     * @return array<string, mixed>
     */
    protected function resolveAppleProvider(
        string $providerToken,
        string $email,
        array &$userAttributes
    ): array {
        if ($providerToken === '') {
            throw ValidationException::withMessages([
                'provider_token' => ['An Apple identity token is required.'],
            ]);
        }

        try {
            $payload = $this->appleTokenVerifier->verify($providerToken);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'provider_token' => ['The Apple identity token is invalid.'],
            ]);
        } catch (RuntimeException) {
            throw ValidationException::withMessages([
                'provider_token' => ['Apple Sign-In is not available right now. Please try again later.'],
            ]);
        }

        if (! empty($payload['email'])) {
            $this->assertTokenEmailMatches($payload['email'], $email, 'apple');
        }

        $this->assertSocialAccountAvailable(SocialAccount::PROVIDER_APPLE, $payload['sub']);

        $userAttributes['password'] = null;

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $userAttributes
     */
    protected function resolveEmailProvider(string $password, array &$userAttributes): null
    {
        if ($password === '') {
            throw ValidationException::withMessages([
                'password' => ['A password is required for email registration.'],
            ]);
        }

        $userAttributes['password'] = $password;

        return null;
    }

    protected function assertTokenEmailMatches(string $tokenEmail, string $requestEmail, string $provider): void
    {
        if (strtolower(trim($tokenEmail)) !== strtolower(trim($requestEmail))) {
            throw ValidationException::withMessages([
                'email' => ["The email address does not match the {$provider} account."],
            ]);
        }
    }

    protected function assertSocialAccountAvailable(string $provider, string $providerUserId): void
    {
        $exists = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'provider_token' => ['This account is already registered. Please sign in instead.'],
            ]);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function moveIdentityDocumentsToUser(IdentityVerificationSession $session, int $userId): array
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($session->id_front_path) || ! $disk->exists($session->id_back_path)) {
            throw ValidationException::withMessages([
                'verification_session_token' => ['The uploaded ID images could not be found. Please restart verification.'],
            ]);
        }

        $frontExtension = pathinfo($session->id_front_path, PATHINFO_EXTENSION) ?: 'jpg';
        $backExtension = pathinfo($session->id_back_path, PATHINFO_EXTENSION) ?: 'jpg';

        $frontDestination = "id-documents/{$userId}/front.{$frontExtension}";
        $backDestination = "id-documents/{$userId}/back.{$backExtension}";

        $disk->makeDirectory("id-documents/{$userId}");
        $disk->move($session->id_front_path, $frontDestination);
        $disk->move($session->id_back_path, $backDestination);

        $sessionDirectory = 'identity-sessions/'.$session->session_token;

        if ($disk->exists($sessionDirectory)) {
            $disk->deleteDirectory($sessionDirectory);
        }

        return [$frontDestination, $backDestination];
    }
}
