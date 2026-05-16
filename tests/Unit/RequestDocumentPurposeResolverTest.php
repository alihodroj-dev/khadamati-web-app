<?php

namespace Tests\Unit;

use App\Models\RequestDocument;
use App\Support\RequestDocumentPurposeResolver;
use Tests\TestCase;

class RequestDocumentPurposeResolverTest extends TestCase
{
    public function test_maps_staff_document_types_to_purpose(): void
    {
        $this->assertSame(
            RequestDocument::PURPOSE_CERTIFICATE,
            RequestDocumentPurposeResolver::fromStaffDocumentType('certificate')
        );

        $this->assertSame(
            RequestDocument::PURPOSE_RECEIPT,
            RequestDocumentPurposeResolver::fromStaffDocumentType('receipt')
        );

        $this->assertSame(
            RequestDocument::PURPOSE_OFFICIAL_RESPONSE,
            RequestDocumentPurposeResolver::fromStaffDocumentType('response')
        );

        $this->assertSame(
            RequestDocument::PURPOSE_OTHER,
            RequestDocumentPurposeResolver::fromStaffDocumentType('misc')
        );
    }
}
