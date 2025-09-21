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
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        User::factory()->hte()->create([
            'username' => 'maria',
            'email' => 'maria@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        User::factory()->adviser()->create([
            'username' => 'emman',
            'email' => 'emman@example.com',
            'status' => 'verified',
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
        $this->call(AcademeAccountSeeder::class);
        $this->call(HTESeeder::class);
        $this->call(AdviserSeeder::class);
        $this->call(InternshipSeeder::class);
        $this->call(ExtendedInternshipSeeder::class);
        $this->call(StudentSeeder::class);
        $this->call(ExtendedStudentSeeder::class);
        $this->call(InternshipCriteriaSeeder::class);
        $this->call(StudentScoreSeeder::class);
        $this->call(StudentMatchSeeder::class);
        $this->call(PlacementSeeder::class);
        $this->call(DeadlineSeeder::class);

    }
}
