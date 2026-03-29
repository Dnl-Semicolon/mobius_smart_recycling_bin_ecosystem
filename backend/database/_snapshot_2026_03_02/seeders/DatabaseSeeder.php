<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin account — use this to log in and demo the admin panel
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@mobius.test',
        ]);

        // Collector account — use this to log in as a waste collector
        User::factory()->collector()->create([
            'name' => 'Collector',
            'email' => 'collector@mobius.test',
        ]);

        // Regular public user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            OutletSeeder::class,
            BinSeeder::class,
            DetectionEventSeeder::class,
            PickupRequestSeeder::class,
        ]);
    }
}
