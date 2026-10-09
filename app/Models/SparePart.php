<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'photo',
        'category_id',
        'stock',
        'min_stock',
        'location',
        'price',
    ];

    protected $casts = [
        'stock' => 'integer',
        'min_stock' => 'integer',
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Kode dibebaskan saat part dihapus, supaya bisa dipakai part baru.
        static::deleting(function (SparePart $part) {
            if (! $part->isForceDeleting()) {
                $part->code = $part->code . '-DEL-' . $part->id;
                $part->saveQuietly();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}