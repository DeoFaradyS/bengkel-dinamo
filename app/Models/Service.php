<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_name', 'phone', 'complaint', 'scheduled_at',
        'status', 'cancel_reason',
    ];

    protected $casts = [
        'status' => ServiceStatus::class,
        'scheduled_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }
}