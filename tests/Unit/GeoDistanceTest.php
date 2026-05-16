<?php

namespace Tests\Unit;

use App\Support\GeoDistance;
use Tests\TestCase;

class GeoDistanceTest extends TestCase
{
    public function test_haversine_returns_expected_distance(): void
    {
        $distance = GeoDistance::haversineKm(33.8938, 35.5018, 33.9000, 35.5000);

        $this->assertGreaterThan(0.5, $distance);
        $this->assertLessThan(1.5, $distance);
    }
}
