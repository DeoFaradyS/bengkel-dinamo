<?php

namespace App\Filament\Widgets\Laporan;

use App\Support\Dashboard\ServiceMetrics;

/** Jasa paling menghasilkan menurut Rupiah (beda dengan "Jasa Terlaris" di dasbor yang menurut jumlah). */
class TopRevenueServicesChart extends RupiahBarChart
{
    public function getHeading(): ?string
    {
        return 'Jasa paling menghasilkan';
    }

    protected function rows(ServiceMetrics $metrics): array
    {
        return $metrics->revenueByService();
    }
}
