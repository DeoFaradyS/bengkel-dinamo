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
 * - Servis selesai dan pendapatan: status Selesai, menurut tanggal jadwal
 *   (sistem belum menyimpan tanggal selesai).
 * - Pendapatan: harga jasa + (jumlah x harga) spare part.
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
        $ids = $this->completedQuery()->select('id');

        $jobs = (float) ServiceItem::query()->whereIn('service_id', $ids)->sum('price');
        $parts = (float) ServicePart::query()->whereIn('service_id', $ids)->sum(DB::raw('quantity * unit_price'));

        return $jobs + $parts;
    }

    public function averageValue(): float
    {
        $count = $this->completedCount();

        return $count === 0 ? 0.0 : $this->revenue() / $count;
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
            ->mapWithKeys(fn ($row) => [$row->status->value => (int) $row->c]);

        return collect(ServiceStatus::cases())
            ->mapWithKeys(fn ($case) => [$case->value => (int) ($counts[$case->value] ?? 0)])
            ->all();
    }

    /** @return array<string, int> */
    public function typeBreakdown(): array
    {
        $counts = $this->bookingsQuery()
            ->selectRaw('booking_type, COUNT(*) as c')
            ->groupBy('booking_type')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->booking_type->value => (int) $row->c]);

        return collect(BookingType::cases())
            ->mapWithKeys(fn ($case) => [$case->value => (int) ($counts[$case->value] ?? 0)])
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
            ->map(fn ($c) => (int) $c)
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
            ->mapWithKeys(fn ($row) => [
                ($names[$row->spare_part_id] ?? 'Part #'.$row->spare_part_id) => (int) $row->q,
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