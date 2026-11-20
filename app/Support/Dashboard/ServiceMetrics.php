<?php

namespace App\Support\Dashboard;

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\ServicePart;
use App\Models\SparePart;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Semua angka dashboard dihitung di sini, widget hanya menampilkan.
 *
 * Aturan hitung:
 * - Booking masuk: tanggal dibuat (created_at).
 * - Servis selesai: status Selesai, menurut tanggal jadwal (sistem belum menyimpan tanggal selesai).
 * - Pendapatan (sejak 20 Nov 2026): dasar uang diterima, yaitu servis Selesai yang sudah lunas,
 *   menurut tanggal lunas (paid_at). Nilainya jasa + (jumlah x harga) spare part + ongkos transport,
 *   sama dengan total servis (aturan 7). Servis Selesai yang belum lunas tidak dihitung sebagai
 *   pendapatan, tapi ditampilkan terpisah sebagai "belum dibayar".
 */
final class ServiceMetrics
{
    private CarbonImmutable $from;

    private CarbonImmutable $to;

    public function __construct(DateTimeInterface $from, DateTimeInterface $to)
    {
        $this->from = CarbonImmutable::instance($from)->startOfDay();
        $this->to = CarbonImmutable::instance($to)->endOfDay();
    }

    public function from(): CarbonImmutable
    {
        return $this->from;
    }

    public function to(): CarbonImmutable
    {
        return $this->to;
    }

    public function days(): int
    {
        return (int) $this->from->diffInDays($this->to->startOfDay()) + 1;
    }

    /** Periode tepat sebelum ini dengan panjang yang sama. */
    public function previous(): self
    {
        $days = $this->days();

        return new self($this->from->subDays($days), $this->to->subDays($days));
    }

    public function bookingsCount(): int
    {
        return $this->bookingsQuery()->count();
    }

    public function completedCount(): int
    {
        return $this->completedQuery()->count();
    }

    public function revenue(): float
    {
        return $this->revenueBreakdown()['total'];
    }

    public function paidCount(): int
    {
        return $this->paidQuery()->count();
    }

    /**
     * Rincian pendapatan periode ini (servis lunas menurut tanggal lunas).
     *
     * @return array{jobs: float, parts: float, transport: float, total: float, count: int}
     */
    public function revenueBreakdown(?string $bookingType = null): array
    {
        $paid = $this->paidQuery()->when($bookingType, fn(Builder $q) => $q->where('booking_type', $bookingType));

        return self::sumServices($paid);
    }

    /** Rata-rata nilai per servis lunas di periode, nol kalau belum ada. */
    public function averageValue(): float
    {
        $count = $this->paidCount();

        return $count === 0 ? 0.0 : $this->revenue() / $count;
    }

    /**
     * Servis Selesai yang belum lunas saat ini (piutang), tidak terikat periode.
     *
     * @return array{jobs: float, parts: float, transport: float, total: float, count: int}
     */
    public static function unpaidNow(?string $bookingType = null): array
    {
        return self::sumServices(self::unpaidQuery($bookingType)->reorder());
    }

    /**
     * Rekap pendapatan per hari, minggu (Senin-Minggu), atau bulan untuk Laporan Pendapatan
     * (aturan 30). Hanya kelompok yang ada pemasukannya, urut dari yang paling lama. Label minggu
     * dan bulan dipotong ke batas periode, contoh periode mulai Rabu 4 Nov: "4 - 8 Nov".
     *
     * @return list<array{label: string, count: int, jobs: float, parts: float, transport: float, total: float}>
     */
    public function recap(string $unit = 'day', ?string $bookingType = null): array
    {
        $rows = $this->paidQuery()
            ->when($bookingType, fn(Builder $q) => $q->where('booking_type', $bookingType))
            ->orderBy('paid_at')
            ->with(['items', 'serviceParts'])
            ->get();

        return $rows
            ->groupBy(fn(Service $s) => match ($unit) {
                'week' => CarbonImmutable::instance($s->paid_at)->startOfWeek(CarbonImmutable::MONDAY)->toDateString(),
                'month' => CarbonImmutable::instance($s->paid_at)->format('Y-m'),
                default => CarbonImmutable::instance($s->paid_at)->toDateString(),
            })
            ->map(function ($group, string $key) use ($unit) {
                $start = CarbonImmutable::parse($unit === 'month' ? $key . '-01' : $key);
                $end = match ($unit) {
                    'week' => $start->endOfWeek(CarbonImmutable::SUNDAY),
                    'month' => $start->endOfMonth(),
                    default => $start,
                };
                $start = $start->max($this->from);
                $end = $end->min($this->to);

                $jobs = (float) $group->sum(fn(Service $s) => $s->labor_total);
                $parts = (float) $group->sum(fn(Service $s) => $s->parts_total);
                $transport = (float) $group->sum(fn(Service $s) => (float) $s->transport_fee);

                return [
                    'label' => match ($unit) {
                        'week' => $start->isSameDay($end) ? $start->translatedFormat('j M') : $start->translatedFormat('j M') . ' - ' . $end->translatedFormat('j M'),
                        'month' => $start->translatedFormat('F Y'),
                        default => $start->translatedFormat('D, j M Y'),
                    },
                    'count' => $group->count(),
                    'jobs' => $jobs,
                    'parts' => $parts,
                    'transport' => $transport,
                    'total' => $jobs + $parts + $transport,
                ];
            })
            ->values()
            ->all();
    }

    /** Servis Selesai yang belum lunas (piutang), tidak terikat periode, urut tanggal servis. */
    public static function unpaidQuery(?string $bookingType = null): Builder
    {
        return Service::query()
            ->where('status', ServiceStatus::Completed->value)
            ->whereNull('paid_at')
            ->when($bookingType, fn(Builder $q) => $q->where('booking_type', $bookingType))
            ->orderBy('scheduled_at');
    }

    /** Satuan waktu menurut panjang periode: sampai 31 hari per hari, sampai 92 hari per minggu, selebihnya per bulan. */
    public function autoUnit(): string
    {
        return $this->days() <= 31 ? 'day' : ($this->days() <= 92 ? 'week' : 'month');
    }

    /**
     * Grafik pendapatan: per hari untuk periode sampai 31 hari, per minggu sampai 92 hari,
     * selebihnya per bulan, supaya batangnya tidak terlalu banyak.
     *
     * @return array{unit: string, labels: list<string>, values: list<float>}
     */
    public function revenueSeries(): array
    {
        $unit = $this->autoUnit();

        $rows = $this->paidQuery()->get(['id', 'paid_at', 'transport_fee'])->load(['items', 'serviceParts']);

        $bucketOf = fn(CarbonImmutable $d) => match ($unit) {
            'day' => $d->toDateString(),
            'week' => $d->startOfWeek()->toDateString(),
            default => $d->format('Y-m'),
        };

        $sums = [];
        foreach ($rows as $s) {
            $key = $bucketOf(CarbonImmutable::instance($s->paid_at));
            $sums[$key] = ($sums[$key] ?? 0) + $s->grand_total;
        }

        $labels = [];
        $values = [];
        $cursor = match ($unit) {
            'day' => $this->from,
            'week' => $this->from->startOfWeek(),
            default => $this->from->startOfMonth(),
        };

        while ($cursor <= $this->to) {
            $key = $bucketOf($cursor);
            $labels[] = match ($unit) {
                'day' => $cursor->translatedFormat('j M'),
                'week' => $cursor->translatedFormat('j M'),
                default => $cursor->translatedFormat('M Y'),
            };
            $values[] = (float) ($sums[$key] ?? 0);
            $cursor = match ($unit) {
                'day' => $cursor->addDay(),
                'week' => $cursor->addWeek(),
                default => $cursor->addMonth(),
            };
        }

        return ['unit' => $unit, 'labels' => $labels, 'values' => $values];
    }

    /** @return array{jobs: float, parts: float, transport: float, total: float, count: int} */
    private static function sumServices(Builder $services): array
    {
        $ids = (clone $services)->select('id');

        $jobs = (float) ServiceItem::query()->whereIn('service_id', $ids)->sum('price');
        $parts = (float) ServicePart::query()->whereIn('service_id', $ids)->sum(DB::raw('quantity * unit_price'));
        $transport = (float) (clone $services)->sum('transport_fee');

        return [
            'jobs' => $jobs,
            'parts' => $parts,
            'transport' => $transport,
            'total' => $jobs + $parts + $transport,
            'count' => (clone $services)->count(),
        ];
    }

    /** Servis lunas dengan tanggal lunas di periode ini (dasar pendapatan). */
    public function paidQuery(): Builder
    {
        return Service::query()
            ->where('status', ServiceStatus::Completed->value)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$this->from, $this->to]);
    }

    /** Persen: (dibatalkan + ditolak) dibagi booking masuk. */
    public function cancellationRate(): float
    {
        $total = $this->bookingsCount();

        if ($total === 0) {
            return 0.0;
        }

        $lost = $this->bookingsQuery()
            ->whereIn('status', [ServiceStatus::Cancelled->value, ServiceStatus::Rejected->value])
            ->count();

        return round($lost / $total * 100, 1);
    }

    /** Booking berstatus Menunggu saat ini, tidak terikat periode. */
    public static function pendingNow(): int
    {
        return Service::query()->where('status', ServiceStatus::Pending->value)->count();
    }

    /** @return array{dates: list<string>, bookings: list<int>, completed: list<int>} */
    public function perDay(): array
    {
        $bookings = $this->bookingsQuery()
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $completed = $this->completedQuery()
            ->selectRaw('DATE(scheduled_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $dates = [];
        $b = [];
        $c = [];

        for ($i = 0; $i < $this->days(); $i++) {
            $date = $this->from->addDays($i)->toDateString();

            $dates[] = $date;
            $b[] = (int) ($bookings[$date] ?? 0);
            $c[] = (int) ($completed[$date] ?? 0);
        }

        return ['dates' => $dates, 'bookings' => $b, 'completed' => $c];
    }

    /** @return array<string, int> */
    public function statusBreakdown(): array
    {
        $counts = $this->bookingsQuery()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($row) => [$row->status->value => (int) $row->c]);

        return collect(ServiceStatus::cases())
            ->mapWithKeys(fn($case) => [$case->value => (int) ($counts[$case->value] ?? 0)])
            ->all();
    }

    /** @return array<string, int> */
    public function typeBreakdown(): array
    {
        $counts = $this->bookingsQuery()
            ->selectRaw('booking_type, COUNT(*) as c')
            ->groupBy('booking_type')
            ->get()
            ->mapWithKeys(fn($row) => [$row->booking_type->value => (int) $row->c]);

        return collect(BookingType::cases())
            ->mapWithKeys(fn($case) => [$case->value => (int) ($counts[$case->value] ?? 0)])
            ->all();
    }

    /** @return array<string, int> nama jasa => berapa kali dipakai */
    public function topServices(int $limit = 5): array
    {
        return ServiceItem::query()
            ->whereIn('service_id', $this->completedQuery()->select('id'))
            ->selectRaw('name, COUNT(*) as c')
            ->groupBy('name')
            ->orderByDesc('c')
            ->orderBy('name')
            ->limit($limit)
            ->pluck('c', 'name')
            ->map(fn($c) => (int) $c)
            ->all();
    }

    /** @return array<string, int> nama spare part => jumlah terpakai */
    public function topParts(int $limit = 5): array
    {
        $rows = ServicePart::query()
            ->whereIn('service_id', $this->completedQuery()->select('id'))
            ->selectRaw('spare_part_id, SUM(quantity) as q')
            ->groupBy('spare_part_id')
            ->orderByDesc('q')
            ->orderBy('spare_part_id')
            ->limit($limit)
            ->get();

        $names = SparePart::withTrashed()
            ->whereIn('id', $rows->pluck('spare_part_id'))
            ->pluck('name', 'id');

        return $rows
            ->mapWithKeys(fn($row) => [
                ($names[$row->spare_part_id] ?? 'Suku cadang #' . $row->spare_part_id) => (int) $row->q,
            ])
            ->all();
    }

    private function bookingsQuery(): Builder
    {
        return Service::query()->whereBetween('created_at', [$this->from, $this->to]);
    }

    private function completedQuery(): Builder
    {
        return Service::query()
            ->where('status', ServiceStatus::Completed->value)
            ->whereBetween('scheduled_at', [$this->from, $this->to]);
    }
}