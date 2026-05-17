<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use stdClass;
use Throwable;

class AppleIdentityTokenVerificationService
{
    private const KEYS_URL = 'https://appleid.apple.com/auth/keys';

    /**
     * @return array{
     *     sub: string,
     *     iss: string,
     *     aud: string|array<int, string>,
     *     exp: int,
     *     email?: string,
     *     email_verified?: bool|string,
     *     is_private_email?: bool|string
     * }
     */
    public function verify(string $identityToken): array
    {
        $clientId = config('services.apple.client_id');
        $issuer = config('services.apple.issuer');

        if (empty($clientId)) {
            throw new RuntimeException('Apple client ID is not configured.');
        }

        if (empty($issuer)) {
            throw new RuntimeException('Apple issuer is not configured.');
        }

        try {
            $decoded = JWT::decode(
                $identityToken,
                JWK::parseKeySet($this->getApplePublicKeys())
            );
        } catch (Throwable) {
            throw new InvalidArgumentException('Invalid Apple identity token.');
        }

        $payload = $this->normalizePayload($decoded);

        if (($payload['iss'] ?? '') !== $issuer) {
            throw new InvalidArgumentException('Invalid token issuer.');
        }

        if (! $this->audienceMatchesClientId($payload['aud'] ?? null, $clientId)) {
            throw new InvalidArgumentException('Invalid token audience.');
        }

        if (($payload['exp'] ?? 0) < time()) {
            throw new InvalidArgumentException('Token has expired.');
        }

        if (empty($payload['sub'])) {
            throw new InvalidArgumentException('Token is missing required claims.');
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getApplePublicKeys(): array
    {
        /** @var array<string, mixed> $keys */
        $keys = Cache::remember('apple_auth_public_keys', 3600, function (): array {
            $response = Http::timeout(10)->get(self::KEYS_URL);

            if (! $response->successful()) {
                throw new RuntimeException('Unable to fetch Apple public keys.');
            }

            return $response->json();
        });

        return $keys;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizePayload(stdClass $decoded): array
    {
        $payload = json_decode(json_encode($decoded), true);

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Invalid Apple identity token.');
        }

        if (isset($payload['email']) && is_string($payload['email'])) {
            $payload['email'] = strtolower(trim($payload['email']));
        }

        return $payload;
    }

    protected function audienceMatchesClientId(mixed $audience, string $clientId): bool
    {
        if (is_array($audience)) {
            return in_array($clientId, $audience, true);
        }

        return is_string($audience) && $audience === $clientId;
    }
}
