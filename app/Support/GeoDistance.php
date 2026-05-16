<?php

namespace App\Support;

class GeoDistance
{
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Great-circle distance in kilometres (Haversine).
     */
    public static function haversineKm(
        float $fromLat,
        float $fromLng,
        float $toLat,
        float $toLng
    ): float {
        $fromLatRad = deg2rad($fromLat);
        $toLatRad = deg2rad($toLat);
        $deltaLat = deg2rad($toLat - $fromLat);
        $deltaLng = deg2rad($toLng - $fromLng);

        $a = sin($deltaLat / 2) ** 2
            + cos($fromLatRad) * cos($toLatRad) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }
}
