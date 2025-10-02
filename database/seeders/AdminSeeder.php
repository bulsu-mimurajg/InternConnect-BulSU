<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        User::factory()->admin()->create([
            'username' => 'faye',
            'email' => 'faye@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);
    }
}
