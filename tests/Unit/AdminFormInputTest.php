<?php

namespace Tests\Unit;

use App\Support\AdminFormInput;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminFormInputTest extends TestCase
{
    #[Test]
    public function it_parses_comma_separated_required_documents(): void
    {
        $this->assertSame(
            ['national_id', 'proof_of_address'],
            AdminFormInput::parseRequiredDocuments('national_id, proof_of_address')
        );
    }

    #[Test]
    public function it_returns_empty_array_for_blank_required_documents(): void
    {
        $this->assertSame([], AdminFormInput::parseRequiredDocuments(null));
        $this->assertSame([], AdminFormInput::parseRequiredDocuments('   '));
    }

    #[Test]
    public function it_coerces_boolean_form_values(): void
    {
        $this->assertTrue(AdminFormInput::boolean('1'));
        $this->assertFalse(AdminFormInput::boolean('0'));
        $this->assertTrue(AdminFormInput::boolean(null, true));
        $this->assertFalse(AdminFormInput::boolean(null, false));
    }

    #[Test]
    public function it_parses_working_hours_json(): void
    {
        $this->assertSame(
            ['monday' => ['09:00', '17:00']],
            AdminFormInput::parseWorkingHours('{"monday":["09:00","17:00"]}')
        );
        $this->assertNull(AdminFormInput::parseWorkingHours(null));
        $this->assertNull(AdminFormInput::parseWorkingHours('not-json'));
    }
}
