<?php

namespace App\Support;

use Illuminate\Http\Request;

class ReportFilters
{
    public function __construct(
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public ?int $officeId = null,
        public ?int $municipalityId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'municipality_id' => ['nullable', 'integer', 'exists:municipalities,id'],
        ];
    }

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate(self::validationRules());

        return self::fromValidated($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            fromDate: isset($validated['from_date']) ? (string) $validated['from_date'] : null,
            toDate: isset($validated['to_date']) ? (string) $validated['to_date'] : null,
            officeId: isset($validated['office_id']) ? (int) $validated['office_id'] : null,
            municipalityId: isset($validated['municipality_id']) ? (int) $validated['municipality_id'] : null,
        );
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'office_id' => $this->officeId,
            'municipality_id' => $this->municipalityId,
        ];
    }
}
