<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ServicePart extends Model
{
    public $timestamps = false;

    protected $fillable = ['service_id', 'spare_part_id', 'quantity', 'unit_price'];

    protected $casts = ['unit_price' => 'decimal:2'];

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (SparePart $part) {
            if (!$part->isForceDeleting()) {
                $part->code = $part->code . '-DEL-' . $part->id;
                $part->saveQuietly();
            }
        });
    }
}