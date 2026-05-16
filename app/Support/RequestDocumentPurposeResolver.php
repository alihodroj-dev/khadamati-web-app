<?php

namespace App\Support;

use App\Models\RequestDocument;

class RequestDocumentPurposeResolver
{
    /**
     * Map staff-selected document type to stored purpose.
     */
    public static function fromStaffDocumentType(string $documentType): string
    {
        return match (strtolower(trim($documentType))) {
            RequestDocument::PURPOSE_CERTIFICATE => RequestDocument::PURPOSE_CERTIFICATE,
            RequestDocument::PURPOSE_RECEIPT => RequestDocument::PURPOSE_RECEIPT,
            'response', 'approval', 'rejection' => RequestDocument::PURPOSE_OFFICIAL_RESPONSE,
            default => RequestDocument::PURPOSE_OTHER,
        };
    }
}
