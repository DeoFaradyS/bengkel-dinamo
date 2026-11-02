<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ServicePart extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['service_id', 'spare_part_id', 'quantity', 'unit_price'];

    protected $casts = ['unit_price' => 'decimal:2'];

    protected static function booted(): void
    {
        // Harga baris baru disalin dari menu Sparepart kalau belum diisi.
        static::creating(function (ServicePart $servicePart) {
            if ($servicePart->unit_price === null) {
                $servicePart->unit_price = SparePart::withTrashed()
                    ->find($servicePart->spare_part_id)?->price ?? 0;
            }
        });

        // Harga tidak boleh diubah. Kalau part diganti, harga ikut part baru.
        static::updating(function (ServicePart $servicePart) {
            $servicePart->unit_price = $servicePart->isDirty('spare_part_id')
                ? (SparePart::withTrashed()->find($servicePart->spare_part_id)?->price ?? 0)
                : $servicePart->getOriginal('unit_price');
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class)->withTrashed();
    }

    protected function subtotal(): Attribute
    {
        return Attribute::get(fn() => (float) ($this->quantity * $this->unit_price));
    }
}