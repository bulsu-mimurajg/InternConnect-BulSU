<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InternshipSeason;
use App\Models\Deadline;
use App\Models\Student;
use Carbon\Carbon;

class InternshipSeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default season for existing data
        $defaultSeason = InternshipSeason::create([
            'name' => 'AY 2024-2025 First Semester (Default)',
            'start_date' => Carbon::now()->subMonths(3),
            'end_date' => Carbon::now()->addMonths(3),
            'status' => 'active',
        ]);

        // Associate existing deadlines with the default season
        Deadline::whereNull('internship_season_id')
            ->update(['internship_season_id' => $defaultSeason->id]);

        // Associate existing students with the default season
        Student::whereNull('internship_season_id')
            ->update(['internship_season_id' => $defaultSeason->id]);

        $this->command->info("Created default internship season and associated existing data.");
        $this->command->info("Default season: {$defaultSeason->name}");
        $this->command->info("Associated deadlines: " . Deadline::where('internship_season_id', $defaultSeason->id)->count());
        $this->command->info("Associated students: " . Student::where('internship_season_id', $defaultSeason->id)->count());
    }
}