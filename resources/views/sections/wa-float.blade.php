@php
  $waLink = \App\Support\WhatsAppLink::make(config('booking.whatsapp'), 'Halo Budi Dinamo, saya mau tanya soal servis.');
@endphp

{{-- Tombol WA melayang. z-40: di bawah header (z-50) dan modal peta (z-60). --}}
<a href="{{ $waLink }}" target="_blank" rel="noopener" aria-label="Chat WhatsApp"
  style="bottom: calc(1.5rem + env(safe-area-inset-bottom, 0px));"
  class="fixed right-6 z-40 flex items-center gap-2 h-14 px-4 rounded-full bg-green-500 text-white font-semibold shadow-lg transition-colors hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
    stroke-linecap="round" stroke-linejoin="round" class="size-6" aria-hidden="true">
    <path d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
  </svg>
  <span class="hidden sm:inline">Chat WhatsApp</span>
</a>