<?php

namespace App\Support;

use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use Filament\Actions\Action;

/**
 * Notifikasi lonceng admin yang seluruh kotaknya bisa diklik (aturan 36).
 * Filament hanya mendukung tombol aksi, jadi notifikasi diberi satu aksi "Buka servis"
 * (membuka detail servis dan menandai notifikasi sudah dibaca), lalu public/css/notifications.css
 * menyembunyikan tombolnya dan merentangkan area kliknya ke seluruh kotak notifikasi.
 */
final class OwnerNotification
{
    public const OPEN_CLASS = 'notif-buka';

    public static function openService(Service $service): Action
    {
        return Action::make('buka')
            ->label('Buka servis')
            ->url(ServiceResource::getUrl('view', ['record' => $service], panel: 'admin'))
            ->markAsRead()
            ->link()
            ->extraAttributes(['class' => self::OPEN_CLASS]);
    }
}
