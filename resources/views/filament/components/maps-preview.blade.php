@if (is_numeric($lat) && is_numeric($lng))
    <div style="width: 100%;">
        <iframe
            src="https://maps.google.com/maps?q={{ $lat }},{{ $lng }}&z=17&output=embed"
            style="display: block; width: 100%; height: 420px; border: 0; border-radius: 8px;"
            loading="lazy"
        ></iframe>
    </div>
@else
    <p style="font-size: 0.875rem; color: #6b7280;">Isi koordinat untuk melihat pratinjau.</p>
@endif