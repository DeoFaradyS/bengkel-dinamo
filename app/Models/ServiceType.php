<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'default_price', 'description', 'photo'];

    protected $casts = ['default_price' => 'decimal:2'];

    // URL foto jasa di disk public, null kalau belum ada foto.
    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->photo
            ? Storage::disk('public')->url($this->photo)
            : null);
    }
}