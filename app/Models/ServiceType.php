<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceType extends Model
{
    // Hapus = arsip (D1). Jasa terhapus hilang dari katalog dan pilihan form,
    // nota lama aman karena nama dan harga sudah disalin ke service_items.
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'default_price', 'description', 'photo'];

    protected $casts = ['default_price' => 'decimal:2'];

    // Slug (D4) dibuat otomatis dari nama, unik termasuk jasa yang diarsip.
    // Nama berubah, slug ikut berubah; URL lama berbasis id tetap dialihkan ke slug.
    protected static function booted(): void
    {
        static::saving(function (ServiceType $type) {
            if (blank($type->slug) || $type->isDirty('name')) {
                $type->slug = self::uniqueSlug($type->name, $type->id);
            }
        });
    }

    public static function uniqueSlug(?string $name, ?int $ignoreId = null): string
    {
        $dasar = Str::slug((string) $name) ?: 'jasa';
        $slug = $dasar;

        for ($i = 2; self::withTrashed()->where('slug', $slug)->when($ignoreId, fn($q) => $q->whereKeyNot($ignoreId))->exists(); $i++) {
            $slug = $dasar . '-' . $i;
        }

        return $slug;
    }

    // URL foto jasa di disk public, null kalau belum ada foto.
    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->photo
            ? Storage::disk('public')->url($this->photo)
            : null);
    }
}