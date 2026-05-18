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

    #[Test]
    public function it_parses_working_hours_from_day_inputs(): void
    {
        $this->assertSame(
            [
                'monday' => ['09:00', '17:00'],
                'wednesday' => ['10:30', '15:45'],
            ],
            AdminFormInput::parseWorkingHours([
                'monday' => ['enabled' => '1', 'start' => '09:00', 'end' => '17:00'],
                'tuesday' => ['start' => '', 'end' => ''],
                'wednesday' => ['enabled' => '1', 'start' => '10:30', 'end' => '15:45'],
            ])
        );
    }

    #[Test]
    public function it_rejects_invalid_working_hour_day_inputs(): void
    {
        $this->assertNull(AdminFormInput::parseWorkingHours([
            'monday' => ['enabled' => '1', 'start' => '17:00', 'end' => '09:00'],
        ]));
    }

    #[Test]
    public function it_treats_empty_working_hour_day_inputs_as_blank(): void
    {
        $value = [
            'monday' => ['start' => '', 'end' => ''],
        ];

        $this->assertFalse(AdminFormInput::workingHoursInputHasValue($value));
        $this->assertNull(AdminFormInput::parseWorkingHours($value));
    }
}
