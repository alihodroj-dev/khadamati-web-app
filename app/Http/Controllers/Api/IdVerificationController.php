<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CitizenIdentityVerificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class IdVerificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CitizenIdentityVerificationService $verificationService
    ) {}

    public function verify(Request $request)
    {
        $user = $request->user();

        if (! $user->isCitizen()) {
            return $this->errorResponse(
                'Only citizens can verify their identity.',
                null,
                403
            );
        }

        $validated = $request->validate([
            'national_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'national_id')->ignore($user->id),
            ],
            'id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $userUpdates = ['national_id' => $validated['national_id']];

        if ($request->hasFile('id_document')) {
            if ($user->id_document_path && Storage::disk('public')->exists($user->id_document_path)) {
                Storage::disk('public')->delete($user->id_document_path);
            }

            $userUpdates['id_document_path'] = $request->file('id_document')->store(
                'id-documents/' . $user->id,
                'public'
            );
        }

        $verification = $this->verificationService->verify(
            $user,
            $validated['national_id'],
            $request->file('id_document')
        );

        $user->update($userUpdates);

        return $this->successResponse(
            $verification,
            'Identity verified successfully.'
        );
    }
}
