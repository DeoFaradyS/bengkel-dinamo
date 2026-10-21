<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'spare_part_id',
        'type',
        'quantity',
        'stock_after',
        'note',
        'service_id',
        'user_id',
    ];

    protected $casts = [
        'type' => StockMovementType::class,
        'quantity' => 'integer',
        'stock_after' => 'integer',
    ];

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class)->withTrashed();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}