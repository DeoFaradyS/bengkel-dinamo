<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('riwayat', [
            // Soft-delete otomatis tersaring. Indeks (user_id, scheduled_at) mendukung urutan ini.
            'services' => Service::query()
                ->where('user_id', $request->user()->id)
                ->orderByDesc('scheduled_at')
                ->orderByDesc('id')
                ->paginate(10),
        ]);
    }
}