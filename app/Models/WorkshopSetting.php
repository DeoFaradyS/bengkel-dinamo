<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopSetting extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'max_distance_km',
        'fuel_price',
    ];
}