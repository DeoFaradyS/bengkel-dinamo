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
use App\Models\WorkshopSetting;
use App\Actions\CreateBooking;
use Illuminate\Validation\ValidationException;
use App\Enums\StockMovementType;
use Illuminate\Support\Carbon;

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
        'distance_km',
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
        'distance_km' => 'float',
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

            $fresh = self::lockForUpdate()->find($this->id);

            if ($fresh->stock_synced_at) {
                return;
            }
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

    // Alasan servis belum boleh dikerjakan, atau null kalau boleh.
    // $record null berarti servis baru: dianggap paling akhir dibuat.
    public static function startBlockerMessage(?self $record, mixed $scheduledAt): ?string
    {
        $running = self::query()
            ->where('status', ServiceStatus::InProgress->value)
            ->when($record, fn($q) => $q->whereKeyNot($record->id))
            ->exists();

        if ($running) {
            return 'Masih ada servis yang sedang dikerjakan.';
        }

        $scheduledAt = Carbon::parse($scheduledAt)->startOfMinute();

        $ahead = self::query()
            ->where('status', ServiceStatus::Confirmed->value)
            ->when($record, fn($q) => $q->whereKeyNot($record->id))
            ->where(function ($q) use ($scheduledAt, $record) {
                $q->where('scheduled_at', '<', $scheduledAt);

                // Jam sama: yang dibuat lebih dulu antre lebih dulu.
                $q->orWhere(function ($q) use ($scheduledAt, $record) {
                    $q->where('scheduled_at', $scheduledAt);

                    if ($record) {
                        $q->where(fn($q) => $q
                            ->where('created_at', '<', $record->created_at)
                            ->orWhere(fn($q) => $q
                                ->where('created_at', $record->created_at)
                                ->where('id', '<', $record->id)));
                    }
                });
            })
            ->orderBy('scheduled_at')->orderBy('created_at')->orderBy('id')
            ->first();

        return $ahead
            ? "Servis atas nama {$ahead->customer_name} lebih dulu antre."
            : null;
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
                'distance_km' => null,
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
            'distance_km' => $result->airKm,
        ]);
    }
}