<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SparePart extends Model
{
    use HasFactory, SoftDeletes;

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
        // Part baru dengan stok awal: catat sebagai stok awal.
        static::created(function (SparePart $part) {
            if ($part->stock > 0) {
                $part->stockMovements()->create([
                    'type' => StockMovementType::Opening,
                    'quantity' => $part->stock,
                    'stock_after' => $part->stock,
                    'note' => 'Stok awal',
                    'user_id' => auth()->id(),
                ]);
            }
        });

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

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function addStock(int $quantity, StockMovementType $type, ?string $note = null, ?int $serviceId = null): StockMovement
    {
        return DB::transaction(function () use ($quantity, $type, $note, $serviceId) {
            $part = static::lockForUpdate()->findOrFail($this->id);
            $after = $part->stock + $quantity;

            if ($after < 0) {
                throw new \RuntimeException("Stok {$part->name} tidak boleh di bawah 0. Sisa {$part->stock}.");
            }

            $part->stock = $after;
            $part->save();
            $this->stock = $after;

            return $part->stockMovements()->create([
                'type' => $type,
                'quantity' => $quantity,
                'stock_after' => $after,
                'note' => $note,
                'service_id' => $serviceId,
                'user_id' => auth()->id(),
            ]);
        });
    }
}