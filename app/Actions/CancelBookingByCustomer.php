<?php

namespace App\Actions;

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

// Pelanggan membatalkan booking sendiri (lewat Cek Booking atau Riwayat).
// Hanya selama Menunggu: setelah Dikonfirmasi, pembatalan lewat bengkel.
// Pemanggil wajib memastikan booking ini milik pelanggan (kode + HP, atau user_id).
class CancelBookingByCustomer
{
    public const REASONS = ['Ganti jadwal', 'Tidak jadi servis', 'Lainnya'];

    public const PREFIX = 'Dibatalkan pelanggan';

    /** @return bool true kalau berhasil dibatalkan, false kalau status sudah bukan Menunggu. */
    public function handle(Service $service, ?string $reason = null): bool
    {
        $reason = in_array($reason, self::REASONS, true) ? $reason : null;

        $cancelled = DB::transaction(function () use ($service, $reason) {
            $fresh = Service::query()->lockForUpdate()->find($service->id);

            if ($fresh === null || $fresh->status !== ServiceStatus::Pending) {
                return false;
            }

            $fresh->forceFill([
                'status' => ServiceStatus::Cancelled,
                'cancel_reason' => $reason ? self::PREFIX . ': ' . $reason : self::PREFIX,
            ])->save();

            $service->setRawAttributes($fresh->getAttributes(), true);

            return true;
        });

        if ($cancelled) {
            $code = substr($service->booking_code, 0, 3) . ' ' . substr($service->booking_code, 3);

            Notification::make()
                ->title('Booking dibatalkan pelanggan')
                ->body("Booking {$code} ({$service->customer_name}) dibatalkan pelanggan" . ($reason ? ": {$reason}." : '.'))
                ->icon('heroicon-o-no-symbol')
                ->danger()
                ->sendToDatabase(User::where('role', UserRole::Owner->value)->get());
        }

        return $cancelled;
    }
}
