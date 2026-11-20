<?php

namespace App\Providers;

use App\Filament\Pages\Dashboard;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\HtmlString;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Pengirim WA (aturan 27): Fonnte kalau token diisi, selain itu mode log.
        $this->app->bind(\App\Support\WhatsApp\WhatsAppSender::class, fn() => filled(config('services.fonnte.token'))
            ? new \App\Support\WhatsApp\FonnteSender(config('services.fonnte.token'))
            : new \App\Support\WhatsApp\LogSender());
        //
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn(): HtmlString => new HtmlString(<<<'HTML'
            <script>
                new MutationObserver(() => {
                    document.querySelectorAll('form:not([novalidate])')
                        .forEach(form => form.setAttribute('novalidate', ''));
                }).observe(document.documentElement, { childList: true, subtree: true });
            </script>
        HTML),
        );
    }
}