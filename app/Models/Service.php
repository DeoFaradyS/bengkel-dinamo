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
use App\Enums\DistanceMethod;
use App\Models\WorkshopSetting;
use App\Actions\CreateBooking;
use Illuminate\Validation\ValidationException;
use App\Enums\StockMovementType;

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
        'address',
        'distance_air_km',
        'distance_road_km',
        'distance_km',
        'distance_method',
        'distance_estimated',
        'fuel_price',
        'transport_fee',
    ];

    protected $casts = [
        'status' => ServiceStatus::class,
        'booking_type' => BookingType::class,
        'latitude' => 'float',
        'longitude' => 'float',
        'scheduled_at' => 'datetime',
        'stock_synced_at' => 'datetime',
        'distance_air_km' => 'float',
        'distance_road_km' => 'float',
        'distance_km' => 'float',
        'distance_method' => DistanceMethod::class,
        'distance_estimated' => 'boolean',
        'fuel_price' => 'integer',
        'transport_fee' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service) {
            if ($service->booking_type !== BookingType::HomeService) {
                $service->fuel_price = null;
                $service->transport_fee = 0;

                return;
            }

            // Sudah dihitung dan jarak tidak berubah: kunci angka lama.
            if ($service->fuel_price !== null && !$service->isDirty('distance_km')) {
                return;
            }

            $fuelPrice = WorkshopSetting::first()?->fuel_price;

            if ($service->distance_km === null || $fuelPrice === null) {
                $service->fuel_price = null;
                $service->transport_fee = 0;

                return;
            }

            $service->fuel_price = (int) $fuelPrice;
            $service->transport_fee = self::transportFee((float) $service->distance_km, (int) $fuelPrice);
        });
    }

    public static function transportFee(float $km, int $fuelPrice): int
    {
        $liters = $km * config('transport.trip_multiplier') / config('transport.km_per_liter');
        $step = config('transport.round_up_to');

        return (int) (ceil(round($liters * $fuelPrice / $step, 6)) * $step);
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
        return Attribute::get(fn() => $this->labor_total + $this->parts_total + (float) $this->transport_fee);
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

                $sparePart->addStock(
                    -(int) $servicePart->quantity,
                    StockMovementType::Usage,
                    "Servis #{$this->id}",
                    $this->id,
                );
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

    public static function withDistance(array $data): array
    {
        $type = $data['booking_type'] ?? null;
        $type = $type instanceof BookingType ? $type : BookingType::tryFrom((string) $type);

        if ($type !== BookingType::HomeService || blank($data['latitude'] ?? null) || blank($data['longitude'] ?? null)) {
            return array_merge($data, [
                'distance_air_km' => null,
                'distance_road_km' => null,
                'distance_km' => null,
                'distance_method' => null,
                'distance_estimated' => false,
            ]);
        }

        $action = app(CreateBooking::class);
        $result = $action->measureDistance((float) $data['latitude'], (float) $data['longitude']);

        // Data bengkel belum diisi: simpan tanpa jarak, sama seperti booking pelanggan.
        if ($result === null) {
            return $data;
        }

        if ($message = $action->rangeError($result)) {
            throw ValidationException::withMessages(['data.latitude' => $message]);
        }

        return array_merge($data, [
            'distance_air_km' => $result->airKm,
            'distance_road_km' => $result->roadKm,
            'distance_km' => $result->officialKm,
            'distance_method' => $result->method,
            'distance_estimated' => $result->estimated,
        ]);
    }
}