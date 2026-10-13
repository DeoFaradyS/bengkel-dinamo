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