<?php

namespace Tests\Unit;

use App\Services\IdentityIdParserService;
use Tests\TestCase;

class IdentityIdParserServiceTest extends TestCase
{
    public function test_parse_returns_empty_values_for_blank_text(): void
    {
        $parser = new IdentityIdParserService;

        $result = $parser->parse('');

        $this->assertSame('', $result['first_name']);
        $this->assertSame('', $result['national_id']);
        $this->assertNull($result['date_of_birth']);
    }

    public function test_parse_extracts_labeled_fields_and_national_id(): void
    {
        $parser = new IdentityIdParserService;

        $ocrText = <<<TEXT
        First Name: Ali
        Last Name: Hodroj
        Father Name: Ahmad
        Mother Name: Sara
        Date of Birth: 1998-05-17
        National ID: 1234567890
        TEXT;

        $result = $parser->parse($ocrText);

        $this->assertSame('Ali', $result['first_name']);
        $this->assertSame('Hodroj', $result['last_name']);
        $this->assertSame('Ahmad', $result['father_name']);
        $this->assertSame('Sara', $result['mother_name']);
        $this->assertSame('1998-05-17', $result['date_of_birth']);
        $this->assertSame('1234567890', $result['national_id']);
    }
}
