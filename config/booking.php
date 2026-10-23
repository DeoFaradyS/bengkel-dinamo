<?php

return [
    'open' => '07:00',
    'close' => '17:00',
    'slot_minutes' => 60,
    'breaks' => [['12:00', '13:00']],
    'capacity' => 1,
    'min_lead_minutes' => 60,   // slot kurang dari 1 jam dari sekarang tidak tampil
    'max_days_ahead' => 14,     // pelanggan bisa booking sampai 14 hari ke depan
    'expire_hours' => 24,       // dipakai nanti (langkah 3)
    'expire_before_minutes' => 60,
];