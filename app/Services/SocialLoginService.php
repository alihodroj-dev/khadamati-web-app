<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use InvalidArgumentException;

class SocialLoginService
{
    public function __construct(
        private GoogleIdTokenVerificationService $googleTokenVerifier,
        private AppleIdentityTokenVerificationService $appleTokenVerifier,
        private GoogleAuthenticationService $googleAuth,
        private AppleAuthenticationService $appleAuth,
    ) {}

    /**
     * @return array{user: User, provider: string}
     */
    public function authenticate(
        string $provider,
        string $idToken,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
    ): array {
        return match ($provider) {
            SocialAccount::PROVIDER_GOOGLE => $this->authenticateGoogle($idToken, $firstName, $lastName),
            SocialAccount::PROVIDER_APPLE => $this->authenticateApple($idToken, $firstName, $lastName, $email),
            default => throw new InvalidArgumentException('Unsupported social login provider.'),
        };
    }

    /**
     * @return array{user: User, provider: string}
     */
    protected function authenticateGoogle(
        string $idToken,
        ?string $firstName,
        ?string $lastName,
    ): array {
        $payload = $this->googleTokenVerifier->verify($idToken);

        $user = $this->googleAuth->authenticate($payload, $firstName, $lastName);

        return [
            'user' => $user,
            'provider' => SocialAccount::PROVIDER_GOOGLE,
        ];
    }

    /**
     * @return array{user: User, provider: string}
     */
    protected function authenticateApple(
        string $idToken,
        ?string $firstName,
        ?string $lastName,
        ?string $email,
    ): array {
        $payload = $this->appleTokenVerifier->verify($idToken);

        $user = $this->appleAuth->authenticate($payload, $firstName, $lastName, $email);

        return [
            'user' => $user,
            'provider' => SocialAccount::PROVIDER_APPLE,
        ];
    }

    public function providerLabel(string $provider): string
    {
        return match ($provider) {
            SocialAccount::PROVIDER_GOOGLE => 'Google',
            SocialAccount::PROVIDER_APPLE => 'Apple',
            default => 'Social',
        };
    }
}
