<?php

use App\Support\PhoneNumber;

it('normalisasi HP ke format 08...', function (string $input) {
    expect(PhoneNumber::normalize($input))->toBe('081234567890');
})->with([
    'sudah benar' => ['081234567890'],
    'pakai +62' => ['+6281234567890'],
    'pakai 62' => ['6281234567890'],
    'pakai spasi dan +62' => ['+62 812 3456 7890'],
    'pakai strip' => ['0812-3456-7890'],
    'spasi di pinggir' => ['  081234567890 '],
]);

it('normalisasi null dan kosong', function () {
    expect(PhoneNumber::normalize(null))->toBeNull()
        ->and(PhoneNumber::normalize(''))->toBe('');
});