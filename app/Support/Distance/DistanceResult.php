<?php

namespace App\Support\Distance;

final readonly class DistanceResult
{
    public function __construct(
        public float $airKm,
    ) {
    }
}