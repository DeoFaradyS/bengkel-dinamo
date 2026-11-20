<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopSetting extends Model
{
    protected $fillable = [
        'phone',
        'latitude',
        'longitude',
        'maps_url',
        'max_distance_km',
        'fuel_price',
    ];
}