<div wire:ignore>
    <div x-data
        x-init="window.adminPinMap
            ? window.adminPinMap($el, $wire)
            : window.addEventListener('admin-map-ready', () => window.adminPinMap($el, $wire), { once: true })"
        class="w-full rounded-lg"
        style="height: 256px; position: relative; z-index: 0;"></div>
</div>