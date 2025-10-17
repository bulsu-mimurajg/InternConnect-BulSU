<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\InternshipSeason;

class AssociateStudentsWithSeason extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:associate-with-season {--season-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate students with an internship season';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $seasonId = $this->option('season-id');
        
        if ($seasonId) {
            $season = InternshipSeason::find($seasonId);
            if (!$season) {
                $this->error("Season with ID {$seasonId} not found.");
                return 1;
            }
        } else {
            // Find the default season
            $season = InternshipSeason::where('name', 'AY 2024-2025 First Semester (Default)')->first();
            if (!$season) {
                $season = InternshipSeason::first();
            }
            
            if (!$season) {
                $this->error('No internship season found.');
                return 1;
            }
        }

        $this->info("Using season: {$season->name}");

        // Get students without seasons
        $studentsWithoutSeason = Student::whereNull('internship_season_id')->get();
        
        if ($studentsWithoutSeason->isEmpty()) {
            $this->info('All students already have seasons assigned.');
            return 0;
        }

        // Associate students with the season
        $updatedCount = Student::whereNull('internship_season_id')
            ->update(['internship_season_id' => $season->id]);

        $this->info("Associated {$updatedCount} students with season: {$season->name}");
        
        // Show summary
        $totalStudents = Student::count();
        $studentsWithSeasons = Student::whereNotNull('internship_season_id')->count();
        
        $this->info("Total students: {$totalStudents}");
        $this->info("Students with seasons: {$studentsWithSeasons}");
        $this->info("Students without seasons: " . ($totalStudents - $studentsWithSeasons));

        return 0;
    }
}
