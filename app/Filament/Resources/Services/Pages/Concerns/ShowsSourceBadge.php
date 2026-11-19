<?php

namespace App\Filament\Resources\Services\Pages\Concerns;

use App\Enums\ServiceSource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

// Badge kecil di samping judul halaman Ubah dan Detail servis: siapa yang membuat
// servis ini (Pelanggan lewat booking online, atau Bengkel lewat Tambah servis).
// Menggantikan kolom Asal di tabel dan entri Asal di detail.
trait ShowsSourceBadge
{
    public function getHeading(): string|Htmlable
    {
        $source = $this->record->source;

        if (!$source instanceof ServiceSource) {
            return $this->getTitle();
        }

        $badge = Blade::render(
            '<x-filament::badge :color="$color" class="align-middle" style="margin-inline-start:.5rem;">Dibuat oleh {{ $label }}</x-filament::badge>',
            ['color' => $source->getColor(), 'label' => $source->getLabel()],
        );

        return new HtmlString('<span style="display:inline-flex;align-items:center;flex-wrap:wrap;gap:.25rem;">'
            . e($this->getTitle()) . $badge . '</span>');
    }
}
