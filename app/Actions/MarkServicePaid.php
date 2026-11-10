<?php

namespace App\Actions;

use App\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class MarkServicePaid
{
    /**
     * Pembayaran tunai: cukup catat kapan lunas. Hanya servis Selesai yang
     * belum lunas. Dikunci supaya dua klik bersamaan tidak menimpa paid_at.
     *
     * @return bool true kalau berhasil ditandai, false kalau tidak memenuhi syarat.
     */
    public function handle(Service $service): bool
    {
        return DB::transaction(function () use ($service) {
            $fresh = Service::query()->lockForUpdate()->find($service->id);

            if ($fresh === null || $fresh->status !== ServiceStatus::Completed || $fresh->paid_at !== null) {
                return false;
            }

            // paid_at tidak di $fillable, jadi lewat forceFill.
            $fresh->forceFill(['paid_at' => now()])->save();

            return true;
        });
    }
}