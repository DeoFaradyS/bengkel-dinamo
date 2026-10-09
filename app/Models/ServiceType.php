<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = ['name', 'default_price'];

    protected $casts = ['default_price' => 'decimal:2'];
}