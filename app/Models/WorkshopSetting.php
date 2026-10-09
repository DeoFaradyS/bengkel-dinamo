<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopSetting extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'max_distance_km',
        'price_per_km',
    ];
}