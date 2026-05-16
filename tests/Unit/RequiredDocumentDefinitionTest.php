<?php

namespace Tests\Unit;

use App\Models\ServiceRequest;
use App\Support\RequiredDocumentDefinition;
use Tests\TestCase;

class RequiredDocumentDefinitionTest extends TestCase
{
    public function test_normalizes_legacy_string_to_object(): void
    {
        $definition = RequiredDocumentDefinition::normalize('National ID copy');

        $this->assertSame('national_id_copy', $definition['key']);
        $this->assertSame('National ID copy', $definition['label']);
        $this->assertTrue($definition['required']);
        $this->assertSame(['jpg', 'jpeg', 'png', 'pdf'], $definition['accepted_types']);
        $this->assertSame(5, $definition['max_size_mb']);
    }

    public function test_resolves_document_type_by_key_or_label(): void
    {
        $definitions = RequiredDocumentDefinition::normalizeList([
            'National ID copy',
        ]);

        $this->assertSame('national_id_copy', RequiredDocumentDefinition::resolveTypeKey('national_id_copy', $definitions));
        $this->assertSame('national_id_copy', RequiredDocumentDefinition::resolveTypeKey('National ID copy', $definitions));
    }

    public function test_missing_documents_compare_by_key_and_tolerate_legacy_labels(): void
    {
        $service = new \App\Models\Service([
            'required_documents' => ['National ID copy', 'Proof of address'],
        ]);

        $serviceRequest = new ServiceRequest([
            'status' => 'pending',
        ]);
        $serviceRequest->setRelation('service', $service);
        $serviceRequest->setRelation('documents', collect([
            new \App\Models\RequestDocument(['document_type' => 'National ID copy']),
        ]));

        $missing = $serviceRequest->missingRequiredDocuments();

        $this->assertCount(1, $missing);
        $this->assertSame('proof_of_address', $missing[0]['key']);
        $this->assertSame('Proof of address', $missing[0]['label']);
    }
}
