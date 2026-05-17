<?php

namespace App\Services;

use App\Models\IdentityVerificationSession;
use App\Models\User;
use App\Support\UserProfileCompletion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileCompletionService
{
    /**
     * @param  array{
     *     verification_session_token: string,
     *     first_name: string,
     *     last_name: string,
     *     father_name: string,
     *     mother_name: string,
     *     date_of_birth: string,
     *     national_id: string,
     *     phone?: string|null
     * }  $data
     */
    public function complete(User $user, array $data): User
    {
        if (UserProfileCompletion::isCompleted($user)) {
            throw ValidationException::withMessages([
                'profile' => ['Your profile is already complete.'],
            ]);
        }

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

        return DB::transaction(function () use ($data, $session, $user) {
            [$idFrontPath, $idBackPath] = $this->moveIdentityDocumentsToUser($session, $user->id);

            $user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'father_name' => $data['father_name'],
                'mother_name' => $data['mother_name'],
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'date_of_birth' => $data['date_of_birth'],
                'national_id' => $data['national_id'],
                'phone' => $data['phone'] ?? $user->phone,
                'id_front_path' => $idFrontPath,
                'id_back_path' => $idBackPath,
            ]);

            $session->update([
                'status' => IdentityVerificationSession::STATUS_CONSUMED,
            ]);

            return $user->fresh();
        });
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
