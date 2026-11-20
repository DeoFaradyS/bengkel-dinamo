<?php

namespace App\Filament\Widgets\Laporan;

use App\Support\Dashboard\ServiceMetrics;

/** Spare part paling menghasilkan menurut Rupiah (jumlah x harga jual). */
class TopRevenuePartsChart extends RupiahBarChart
{
    protected string $color = '#22c55e';

    public function getHeading(): ?string
    {
        return 'Spare part paling menghasilkan';
    }

    protected function rows(ServiceMetrics $metrics): array
    {
        return $metrics->revenueByPart();
    }
}
