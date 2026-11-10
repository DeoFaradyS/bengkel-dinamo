@props(['status' => null, 'label' => null, 'color' => null])

@php
    if ($status) {
        $label ??= $status->getLabel();
        $color ??= method_exists($status, 'getColor') ? $status->getColor() : null;
    }

    // Warna sama dengan getColor() di panel admin.
    $map = [
        'gray' => 'bg-neutral-100 text-neutral-600 ring-neutral-500/20',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'success' => 'bg-green-50 text-green-700 ring-green-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
    ];
    $color = is_string($color) && isset($map[$color]) ? $color : 'gray';
@endphp

<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $map[$color] }}">
    {{ $label }}
</span>