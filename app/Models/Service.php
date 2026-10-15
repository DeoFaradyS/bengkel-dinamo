<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\BookingType;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_name',
        'phone',
        'complaint',
        'scheduled_at',
        'status',
        'cancel_reason',
        'booking_type',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'status' => ServiceStatus::class,
        'booking_type' => BookingType::class,
        'latitude' => 'float',
        'longitude' => 'float',
        'scheduled_at' => 'datetime',
        'stock_synced_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }

    public function serviceParts(): HasMany
    {
        return $this->hasMany(ServicePart::class);
    }

    protected function laborTotal(): Attribute
    {
        return Attribute::get(fn() => (float) $this->items->sum('price'));
    }

    protected function partsTotal(): Attribute
    {
        return Attribute::get(fn() => (float) $this->serviceParts->sum('subtotal'));
    }

    protected function grandTotal(): Attribute
    {
        return Attribute::get(fn() => $this->labor_total + $this->parts_total);
    }

    public function syncStock(): void
    {
        if ($this->stock_synced_at) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->serviceParts()->get() as $servicePart) {
                $sparePart = SparePart::lockForUpdate()->findOrFail($servicePart->spare_part_id);

                if ($sparePart->stock < $servicePart->quantity) {
                    throw new \RuntimeException("Stok {$sparePart->name} kurang. Sisa {$sparePart->stock}.");
                }

                $sparePart->decrement('stock', $servicePart->quantity);
            }

            $this->forceFill(['stock_synced_at' => now()])->save();
        });
    }

    public function invoiceNumber(): string
    {
        return 'INV-'
            . $this->scheduled_at->format('Ymd')
            . '-'
            . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function invoicePdf(): string
    {
        $this->load(['items', 'serviceParts.sparePart']);

        return Pdf::loadView('invoices.service', [
            'service' => $this,
            'invoiceNumber' => $this->invoiceNumber(),
        ])->setPaper('a4')->output();
    }
}