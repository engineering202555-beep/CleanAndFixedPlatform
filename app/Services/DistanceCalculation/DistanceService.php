<?php

namespace App\Services\DistanceCalculation;

class DistanceService
{
    public function calculate(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2
    ): float {
        $earthRadius = 6371;

        $lat1 = deg2rad($latitude1);
        $lat2 = deg2rad($latitude2);

        $deltaLat = deg2rad($latitude2 - $latitude1);
        $deltaLon = deg2rad($longitude2 - $longitude1);

        $a =
            sin($deltaLat / 2) ** 2
            +
            cos($lat1)
            * cos($lat2)
            * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return round($earthRadius * $c, 2);
    }
}