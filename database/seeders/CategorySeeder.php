<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Sikat Arang' => 'Carbon brush dan holder',
            'Bearing' => 'Laher dan bushing',
            'Kumparan' => 'Armature, stator, field coil, kawat email',
            'Starter' => 'Bendix, solenoid, gear starter',
            'Alternator' => 'Dioda, regulator, rectifier',
            'Lainnya' => null,
        ];

        foreach ($categories as $name => $description) {
            Category::firstOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
        }
    }
}