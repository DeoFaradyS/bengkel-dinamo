<?php

namespace App\Enums;

enum DistanceMethod: string
{
    case Haversine = 'haversine';
    case Ors = 'ors';
}