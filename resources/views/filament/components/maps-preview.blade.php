{{-- Pratinjau lokasi bengkel dari koordinat yang terbaca dari link Google Maps. --}}
@if (is_numeric($lat) && is_numeric($lng))
    <div style="height: 240px;">
        <iframe
            src="https://maps.google.com/maps?q={{ $lat }},{{ $lng }}&z=17&output=embed"
            style="display: block; width: 100%; height: 100%; border: 0; border-radius: 8px;"
            loading="lazy"
        ></iframe>
    </div>
@else
    <p style="font-size: 0.875rem; color: #6b7280;">Tempel link Google Maps untuk melihat pratinjau lokasi.</p>
@endif
