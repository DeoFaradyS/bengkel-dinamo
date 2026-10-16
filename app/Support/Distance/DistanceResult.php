<?php

namespace App\Support\Distance;

use App\Enums\DistanceMethod;

final readonly class DistanceResult
{
    public function __construct(
        public float $airKm,
        public ?float $roadKm,
        public float $officialKm,
        public DistanceMethod $method,
        public bool $estimated,
    ) {
    }
}