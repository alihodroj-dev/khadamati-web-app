<?php

namespace App\Services;

use App\Models\IdentityVerificationSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Throwable;

class IdentityPreviewService
{
    private const SESSION_TTL_HOURS = 24;

    public function __construct(
        private OcrSpaceService $ocrSpaceService,
        private IdentityIdParserService $identityIdParser,
        private IdentityPreviewFormBuilder $formBuilder
    ) {}

    /**
     * @return array{
     *     verification_session_token: string,
     *     fields: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         value: null|string,
     *         editable: bool,
     *         required: bool
     *     }>
     * }
     */
    public function createPreview(UploadedFile $idFront, UploadedFile $idBack): array
    {
        $sessionToken = Str::random(64);
        $basePath = 'identity-sessions/'.$sessionToken;

        $frontPath = $idFront->storeAs($basePath, $this->buildStoredFilename('front', $idFront), 'public');
        $backPath = $idBack->storeAs($basePath, $this->buildStoredFilename('back', $idBack), 'public');

        $ocrRawText = null;
        $status = IdentityVerificationSession::STATUS_PENDING;

        try {
            $ocrRawText = $this->ocrSpaceService->extractText($idFront);
        } catch (Throwable) {
            $status = IdentityVerificationSession::STATUS_FAILED;
        }

        $extractedData = $this->identityIdParser->parse($ocrRawText ?? '');

        $session = IdentityVerificationSession::create([
            'session_token' => $sessionToken,
            'id_front_path' => $frontPath,
            'id_back_path' => $backPath,
            'ocr_raw_text' => $ocrRawText,
            'extracted_data' => $extractedData,
            'status' => $status,
            'expires_at' => now()->addHours(self::SESSION_TTL_HOURS),
        ]);

        return [
            'verification_session_token' => $session->session_token,
            'fields' => $this->formBuilder->build($extractedData),
        ];
    }

    protected function buildStoredFilename(string $side, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension()
            ?: $file->extension()
            ?: 'bin';

        return $side.'.'.strtolower($extension);
    }
}
