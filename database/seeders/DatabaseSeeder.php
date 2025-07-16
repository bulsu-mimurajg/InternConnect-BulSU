<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RolePermissionSeeder::class);

        User::factory()->admin()->create([
            'username' => 'faye',
            'email' => 'faye@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->student()->create([
            'username' => 'jane',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
