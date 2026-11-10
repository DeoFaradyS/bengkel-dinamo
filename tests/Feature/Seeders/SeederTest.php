<?php

use App\Models\Service;
use App\Models\SparePart;

it('SED-001: DatabaseSeeder jalan tanpa error dan mengisi data', function () {
    $this->seed();

    expect(SparePart::count())->toBeGreaterThan(0)
        ->and(Service::count())->toBeGreaterThan(0);
});