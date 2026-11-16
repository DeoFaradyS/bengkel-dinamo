<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('LGO-001: navbar publik memakai gambar logo yang mengarah ke beranda', function () {
    expect(public_path('images/logo-dinamo.png'))->toBeFile();

    $this->get('/')
        ->assertOk()
        ->assertSee('images/logo-dinamo.png')
        ->assertSee('alt="Dinamo, Bengkel Cak Budi"', false);
});

test('LGO-002: panel admin memakai logo yang sama', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);

    $this->actingAs($owner)->get('/admin')
        ->assertOk()
        ->assertSee('images/logo-dinamo.png');
});
