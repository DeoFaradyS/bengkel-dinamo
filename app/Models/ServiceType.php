<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ServiceType extends Model
{
    // Hapus = arsip (D1). Jasa terhapus hilang dari katalog dan pilihan form,
    // nota lama aman karena nama dan harga sudah disalin ke service_items.
    use HasFactory, SoftDeletes;

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