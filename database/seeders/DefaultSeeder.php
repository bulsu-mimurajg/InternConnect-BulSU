<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultSeeder extends Seeder
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
            'email_verified_at' => now(),
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        $this->call(CategorySeeder::class);
        $this->call(HTEAssessmentSeeder::class);

//        $this->call(SectionSeeder::class);
//        $this->call(SubCategorySeeder::class);
//        $this->call(QuestionSeeder::class);
    }
}
