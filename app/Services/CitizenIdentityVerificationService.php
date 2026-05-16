<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class CitizenIdentityVerificationService
{
    /**
     * Verify a citizen's identity.
     *
     * Replace this mock implementation with a call to the external
     * identity provider when the real API is available.
     *
     * @return array{
     *     full_name: string,
     *     national_id: string,
     *     date_of_birth: null|string,
     *     verification_status: string
     * }
     */
    public function verify(User $user, string $nationalId, ?UploadedFile $idDocument = null): array
    {
        return $this->mockVerify($user, $nationalId, $idDocument);
    }

    /**
     * @return array{
     *     full_name: string,
     *     national_id: string,
     *     date_of_birth: null|string,
     *     verification_status: string
     * }
     */
    protected function mockVerify(User $user, string $nationalId, ?UploadedFile $idDocument): array
    {
        return [
            'full_name' => $user->name,
            'national_id' => $nationalId,
            'date_of_birth' => null,
            'verification_status' => 'verified',
        ];
    }
}
