<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmptyStateSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@mobius.test',
        ]);

        User::factory()->collector()->create([
            'name' => 'Collector',
            'email' => 'collector@mobius.test',
        ]);

        User::factory()->publicUser()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Daniel Tan',
            'email' => 'daniel@mobius.test',
            'role' => UserRole::PublicUser,
            'roles' => ['public_user', 'store_owner', 'collector'],
        ]);

        User::factory()->create([
            'name' => 'Sarah Lim',
            'email' => 'storeowner@mobius.test',
            'role' => UserRole::StoreOwner,
            'roles' => ['store_owner'],
        ]);
    }
}
