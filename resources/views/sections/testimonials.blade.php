<section class="py-20 bg-white overflow-hidden">
    <div class="max-w-3xl mx-auto text-center px-6 mb-12">
        <h2 class="text-6xl font-bold text-neutral-900">Kata Pelanggan Kami</h2>
        <p class="mt-3.5 text-neutral-500">Ulasan asli dari pelanggan sekitar Pasuruan</p>
    </div>

    <style>
        @keyframes marquee-left {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        @keyframes marquee-right {
            from {
                transform: translateX(-50%);
            }

            to {
                transform: translateX(0);
            }
        }

        .marquee-left {
            animation: marquee-left 40s linear infinite;
        }

        .marquee-right {
            animation: marquee-right 40s linear infinite;
        }

        .marquee-row:hover .marquee-left,
        .marquee-row:hover .marquee-right {
            animation-play-state: paused;
        }
    </style>

    @php
        $reviews = [
            ['Starter mobil saya mati total, langsung dibawa ke Budi Dinamo dan besoknya udah bisa jalan lagi.', 'Nguling', '10 Mar 2024', 'Slamet Riyadi', 'Pemilik Mobil'],
            ['Rewinding cepat, hasil rapi, mesin langsung normal kembali. Pelayanan responsif dan harga masuk akal.', 'Bangil', '14 Feb 2024', 'Budi Santoso', 'Pemilik Pabrik'],
            ['Diagnosanya akurat, gak asal ganti part. Jadi lebih hemat dan servisnya jelas.', 'Grati', '12 Jan 2024', 'Dedi Prasetyo', 'Sopir Angkutan'],
            ['Tim-nya profesional, jelasin masalahnya dulu sebelum kerjain. Recommended.', 'Rembang', '2 Feb 2024', 'Agus Wijaya', 'Pemilik Mobil'],
        ];
        $reviews2 = [
            ['Dari cek sampai selesai prosesnya jelas, alternator langsung normal lagi.', 'Kraton', '18 Okt 2024', 'Hendra Kurniawan', 'Pemilik Mobil'],
            ['Udah langganan servis dinamo di sini bertahun-tahun, selalu puas dan tepercaya.', 'Pandaan', '23 Sep 2025', 'Rudi Hartono', 'Pemilik Armada'],
            ['Komunikasi enak, harga wajar, hasil kerjaannya rapi buat mobil operasional kantor.', 'Winongan', '12 Jan 2024', 'Hanafi Yusuf', 'Pemilik Usaha'],
            ['Servisnya cepat, rapi, harganya juga terjangkau.', 'Lekok', '5 Apr 2024', 'Oktavianus Setiawan', 'Pemilik Mobil'],
        ];
    @endphp

    <div class="marquee-row overflow-hidden mb-6">
        <div class="marquee-left flex gap-6 w-max">
            @foreach (array_merge($reviews, $reviews) as [$text, $city, $date, $name, $role])
                <div class="w-[380px] h-56 shrink-0 bg-neutral-50 rounded-2xl p-6 flex flex-col">
                    <p class="text-neutral-700 flex-1 overflow-hidden line-clamp-4">"{{ $text }}"</p>
                    <div class="pb-4 border-b border-neutral-200"></div>
                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <p class="font-semibold text-neutral-900">{{ $name }}</p>
                            <p class="text-sm text-neutral-500">{{ $role }}, {{ $city }}</p>
                        </div>
                        <span class="text-sm text-neutral-400">{{ $date }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="marquee-row overflow-hidden">
        <div class="marquee-right flex gap-6 w-max">
            @foreach (array_merge($reviews2, $reviews2) as [$text, $city, $date, $name, $role])
                <div class="w-[380px] h-56 shrink-0 bg-neutral-50 rounded-2xl p-6 flex flex-col">
                    <p class="text-neutral-700 flex-1 overflow-hidden line-clamp-4">"{{ $text }}"</p>
                    <div class="pb-4 border-b border-neutral-200"></div>
                    <div class="flex items-center justify-between mt-4">
                        <div>
                            <p class="font-semibold text-neutral-900">{{ $name }}</p>
                            <p class="text-sm text-neutral-500">{{ $role }}, {{ $city }}</p>
                        </div>
                        <span class="text-sm text-neutral-400">{{ $date }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>