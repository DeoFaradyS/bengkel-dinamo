<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Catatan satu pesan WhatsApp otomatis ke pelanggan (aturan 27).
class WhatsappMessage extends Model
{
    protected $fillable = ['service_id', 'event', 'phone', 'message', 'sender', 'success', 'detail'];

    protected $casts = [
        'event' => ServiceStatus::class,
        'success' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
