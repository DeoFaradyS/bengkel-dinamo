<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\DistanceMethod;

class WorkshopSetting extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'max_distance_km',
        'price_per_km',
        'distance_method',
    ];

    protected $casts = [
        'distance_method' => DistanceMethod::class,
    ];
}