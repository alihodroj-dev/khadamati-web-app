<?php

namespace Database\Seeders\Concerns;

use App\Models\ServiceRequest;
use Illuminate\Support\Str;

trait GeneratesReferenceNumbers
{
    protected function uniqueReferenceNumber(string $suffix): string
    {
        $reference = 'KHR-'.now()->format('Ymd').'-'.strtoupper($suffix);

        if (ServiceRequest::where('reference_number', $reference)->exists()) {
            return $this->uniqueReferenceNumber(Str::random(6));
        }

        return $reference;
    }
}
