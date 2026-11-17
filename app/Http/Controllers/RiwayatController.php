<?php

namespace App\Http\Controllers;

use App\Actions\CancelBookingByCustomer;
use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        // Owner tidak punya riwayat booking, arahkan ke panel.
        if ($request->user()->role === UserRole::Owner) {
            return redirect('/admin');
        }

        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $sort = $request->query('sort') === 'total' ? 'total' : 'tanggal';
        $arah = $request->query('arah') === 'asc' ? 'asc' : 'desc';

        $status = ServiceStatus::tryFrom((string) $request->query('status'));
        $tipe = BookingType::tryFrom((string) $request->query('tipe'));
        $bayar = in_array($request->query('bayar'), ['lunas', 'belum_lunas'], true)
            ? $request->query('bayar')
            : '';

        $services = Service::query()
            ->where('user_id', $request->user()->id)
            ->withSum('items', 'price')
            ->withSum('serviceParts as parts_sum', DB::raw('quantity * unit_price'))
            ->when($status, fn ($q) => $q->where('status', $status->value))
            ->when($tipe, fn ($q) => $q->where('booking_type', $tipe->value))
            ->when($bayar === 'lunas', fn ($q) => $q
                ->where('status', ServiceStatus::Completed->value)->whereNotNull('paid_at'))
            ->when($bayar === 'belum_lunas', fn ($q) => $q
                ->where('status', ServiceStatus::Completed->value)->whereNull('paid_at'))
            ->when(
                $sort === 'total',
                fn ($q) => $q->orderByRaw(
                    '(COALESCE(items_sum_price, 0) + COALESCE(parts_sum, 0) + COALESCE(transport_fee, 0)) '.$arah
                ),
                // Indeks (user_id, scheduled_at) mendukung urutan ini.
                fn ($q) => $q->orderBy('scheduled_at', $arah),
            )
            ->orderBy('id', $arah)
            ->paginate($perPage)
            ->withQueryString();

        return view('riwayat', [
            'services' => $services,
            'perPage' => $perPage,
            'sort' => $sort,
            'arah' => $arah,
            'status' => $status?->value ?? '',
            'tipe' => $tipe?->value ?? '',
            'bayar' => $bayar,
            'filtered' => $status !== null || $tipe !== null || $bayar !== '',
            'statusOptions' => collect(ServiceStatus::cases())
                ->mapWithKeys(fn (ServiceStatus $s) => [$s->value => $s->getLabel()]),
            'tipeOptions' => collect(BookingType::cases())
                ->mapWithKeys(fn (BookingType $t) => [$t->value => $t->getLabel()]),
        ]);
    }

    public function show(Request $request, int $id): View|RedirectResponse
    {
        if ($request->user()->role === UserRole::Owner) {
            return redirect('/admin');
        }

        // Hanya booking milik sendiri. Milik orang lain atau yang sudah dihapus: 404.
        $service = Service::query()
            ->where('user_id', $request->user()->id)
            ->with(['items', 'serviceParts.sparePart'])
            ->findOrFail($id);

        return view('riwayat-detail', ['service' => $service]);
    }

    // Pelanggan membatalkan booking miliknya sendiri, hanya selama Menunggu.
    public function batal(Request $request, int $id): RedirectResponse
    {
        if ($request->user()->role === UserRole::Owner) {
            return redirect('/admin');
        }

        $service = Service::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $ok = app(CancelBookingByCustomer::class)->handle($service, $request->input('alasan'));

        return redirect()->route('riwayat.show', $service->id)->with(
            'status',
            $ok ? 'Booking dibatalkan. Kamu bisa langsung booking lagi.' : 'Booking ini sudah tidak bisa dibatalkan dari sini. Hubungi bengkel lewat WhatsApp.',
        );
    }

    public function invoice(Request $request, int $id): Response|RedirectResponse
    {
        if ($request->user()->role === UserRole::Owner) {
            return redirect('/admin');
        }

        // Hanya booking milik sendiri dan hanya yang sudah selesai.
        $service = Service::query()
            ->where('user_id', $request->user()->id)
            ->where('status', ServiceStatus::Completed->value)
            ->findOrFail($id);

        return response($service->invoicePdf(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$service->invoiceNumber().'.pdf"',
        ]);
    }
}