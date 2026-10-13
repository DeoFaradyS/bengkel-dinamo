<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(fn() => actingAs(User::factory()->create()))
    ->in('Feature');

// Data form servis valid. Nomor HP unik supaya tidak kena aturan unique.
function serviceFormData(array $override = []): array
{
    return array_merge([
        'customer_name' => 'Pak Budi',
        'phone' => fake()->unique()->numerify('08##########'),
        'complaint' => 'Dinamo tidak mau engkol',
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'status' => 'confirmed',
        'items' => [],
        'serviceParts' => [],
    ], $override);
}