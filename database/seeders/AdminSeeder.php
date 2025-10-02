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
        $this->call(SectionSeeder::class);

        User::factory()->admin()->create([
            'username' => 'faye',
            'email' => 'faye@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        // Create adviser user if it doesn't exist
        $adviserUser = User::firstOrCreate(
            ['email' => 'lesutemp@gmail.com'],
            [
                'username' => 'adv',
                'status' => 'verified',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign adviser role if not already assigned
        if (!$adviserUser->hasRole('adviser')) {
            $adviserUser->assignRole('adviser');
        }

        // Create adviser profile if it doesn't exist
        $adviser = \App\Models\Adviser::firstOrCreate(
            ['user_id' => $adviserUser->id],
            [
                'adviser_fname' => 'Lesu',
                'adviser_lname' => 'Temp',
                'is_active' => true,
            ]
        );

        // Associate adviser with section '3A-G1' if not already associated
        $section = \App\Models\Section::where('section_name', '3A-G1')->first();
        if ($section && !$adviser->sections()->where('sections.section_id', $section->section_id)->exists()) {
            $adviser->sections()->attach($section->section_id);
        }


    }
}
