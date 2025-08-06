<?php

namespace Database\Seeders;

use App\Models\Student;
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

        User::factory()->hte()->create([
            'username' => 'maria',
            'email' => 'maria@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->adviser()->create([
            'username' => 'emman',
            'email' => 'emman@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->student()->create([
            'username' => 'clairo',
            'email' => 'clairo@example.com',
            'password' => bcrypt('password'),
        ]);

        // Student::factory()->count(20)->create();

        $this->call(CategorySeeder::class);
        $this->call(SectionSeeder::class);

    }
}
