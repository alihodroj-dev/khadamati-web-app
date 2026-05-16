<?php

namespace App\Services;

use Google\Client;
use InvalidArgumentException;
use RuntimeException;

class GoogleIdTokenVerificationService
{
    private const VALID_ISSUERS = [
        'accounts.google.com',
        'https://accounts.google.com',
    ];

    /**
     * @return array{
     *     sub: string,
     *     email: string,
     *     email_verified: bool,
     *     name?: string,
     *     picture?: string,
     *     iss: string,
     *     aud: string,
     *     exp: int
     * }
     */
    public function verify(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        if (empty($clientId)) {
            throw new RuntimeException('Google client ID is not configured.');
        }

        $client = new Client(['client_id' => $clientId]);
        $payload = $client->verifyIdToken($idToken);

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Invalid Google ID token.');
        }

        $issuer = $payload['iss'] ?? '';

        if (! in_array($issuer, self::VALID_ISSUERS, true)) {
            throw new InvalidArgumentException('Invalid token issuer.');
        }

        if (($payload['aud'] ?? '') !== $clientId) {
            throw new InvalidArgumentException('Invalid token audience.');
        }

        $expiresAt = $payload['exp'] ?? 0;

        if ($expiresAt < time()) {
            throw new InvalidArgumentException('Token has expired.');
        }

        if (empty($payload['email_verified'])) {
            throw new InvalidArgumentException('Google email is not verified.');
        }

        if (empty($payload['sub']) || empty($payload['email'])) {
            throw new InvalidArgumentException('Token is missing required claims.');
        }

        return $payload;
    }
}
