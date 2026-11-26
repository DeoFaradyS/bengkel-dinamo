<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;


class ServicePart extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'spare_part_id', 'quantity', 'unit_price'];

    protected $casts = ['unit_price' => 'decimal:2', 'quantity' => \App\Casts\Quantity::class, 'warranty_days' => 'integer'];

    protected static function booted(): void
    {
        // Harga baris baru disalin dari menu Sparepart kalau belum diisi.
        static::creating(function (ServicePart $servicePart) {
            if ($servicePart->unit_price === null) {
                $servicePart->unit_price = SparePart::withTrashed()
                    ->find($servicePart->spare_part_id)?->price ?? 0;
            }

            // Garansi selalu disalin dari menu Suku Cadang (aturan 38).
            $servicePart->warranty_days = self::masterWarranty($servicePart->spare_part_id);
        });

        // Harga tidak boleh diubah. Kalau part diganti, harga ikut part baru.
        static::updating(function (ServicePart $servicePart) {
            $servicePart->unit_price = $servicePart->isDirty('spare_part_id')
                ? (SparePart::withTrashed()->find($servicePart->spare_part_id)?->price ?? 0)
                : $servicePart->getOriginal('unit_price');

            $servicePart->warranty_days = $servicePart->isDirty('spare_part_id')
                ? self::masterWarranty($servicePart->spare_part_id)
                : $servicePart->getOriginal('warranty_days');
        });
    }

    private static function masterWarranty(?int $sparePartId): ?int
    {
        $days = SparePart::withTrashed()->find($sparePartId)?->warranty_days;

        return $days > 0 ? $days : null;
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

    // Garansi dihitung sejak servis lunas (Tandai Lunas hanya untuk servis Selesai).
    // Berlaku sampai akhir hari "lunas + masa garansi". Null kalau tanpa garansi atau belum lunas.
    public function warrantyUntil(): ?Carbon
    {
        $paidAt = $this->service?->paid_at;

        return $this->warranty_days && $paidAt
            ? $paidAt->copy()->startOfDay()->addDays($this->warranty_days)->endOfDay()
            : null;
    }

    public function warrantyActive(): bool
    {
        return $this->warrantyUntil()?->isFuture() ?? false;
    }

    public function warrantyLabel(): string
    {
        if (! $this->warranty_days) {
            return 'Tanpa garansi';
        }

        // Servis batal, ditolak, atau kedaluwarsa tidak pernah lunas, jadi garansinya tidak berlaku.
        if ($this->service?->status?->isFinal() && $this->service->status !== \App\Enums\ServiceStatus::Completed) {
            return '-';
        }

        $until = $this->warrantyUntil();

        return $this->warranty_days . ' hari ' . ($until
            ? '(s/d ' . $until->translatedFormat('j M Y') . ')'
            : '(berlaku setelah lunas)');
    }
}
