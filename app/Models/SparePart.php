<?php

namespace App\Models;

use App\Casts\Quantity;
use App\Enums\StockMovementType;
use App\Enums\StockUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Enums\StockReason;

class SparePart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'unit',
        'photo',
        'category_id',
        'stock',
        'min_stock',
        'storage_location',
        'price',
    ];

    protected $casts = [
        'unit' => StockUnit::class,
        'stock' => Quantity::class,
        'min_stock' => Quantity::class,
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Part baru dengan stok awal: catat sebagai stok awal.
        static::created(function (SparePart $part) {
            if ($part->stock > 0) {
                $part->stockMovements()->create([
                    'type' => StockMovementType::Opening,
                    'quantity_change' => $part->stock,
                    'stock_after' => $part->stock,
                    'note' => 'Stok awal',
                    'user_id' => auth()->id(),
                ]);
            }
        });

        // Kode dibebaskan saat part dihapus, supaya bisa dipakai part baru.
        static::deleting(function (SparePart $part) {
            if (!$part->isForceDeleting()) {
                $part->code = $part->code . '-DEL-' . $part->id;
                $part->saveQuietly();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Satu-satunya jalan mengubah stok (aturan 1). Jumlah boleh desimal hanya untuk satuan kg,
     * meter, liter (aturan 33); satuan hitungan (pcs, set, pasang) harus bulat.
     */
    public function addStock(int|float $quantity, StockMovementType $type, ?string $note = null, ?int $serviceId = null, ?StockReason $reason = null): StockMovement
    {
        return DB::transaction(function () use ($quantity, $type, $note, $serviceId, $reason) {
            $part = static::lockForUpdate()->findOrFail($this->id);
            $quantity = round($quantity, 2);

            if (! ($part->unit ?? StockUnit::Pcs)->allowsDecimal() && floor($quantity) != $quantity) {
                throw new \RuntimeException("Jumlah {$part->name} harus bilangan bulat (satuan {$part->unit->label()}).");
            }

            $after = round($part->stock + $quantity, 2);

            if ($after < 0) {
                throw new \RuntimeException("Stok {$part->name} tidak boleh di bawah 0. Sisa {$part->stock}.");
            }

            $part->stock = $after;
            $part->save();
            $this->stock = $after;

            return $part->stockMovements()->create([
                'type' => $type,
                'quantity_change' => $quantity,
                'stock_after' => $after,
                'note' => $note,
                'reason' => $reason,
                'service_id' => $serviceId,
                'user_id' => auth()->id(),
            ]);
        });
    }
}