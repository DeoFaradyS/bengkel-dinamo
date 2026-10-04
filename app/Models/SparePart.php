<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'category', 'stock', 'min_stock', 'location', 'price',
    ];
}