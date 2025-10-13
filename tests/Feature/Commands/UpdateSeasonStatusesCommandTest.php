<?php

namespace Tests\Feature\Commands;

use App\Console\Commands\UpdateSeasonStatusesCommand;
use App\Models\InternshipSeason;
use App\Models\Deadline;
use App\Services\InternshipSeasonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UpdateSeasonStatusesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_updates_season_statuses_correctly()
    {
        // Create a season that should be active (within date range and has all deadlines)
        $activeSeason = InternshipSeason::factory()->create([
            'name' => 'Test Season',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
            'status' => 'inactive',
        ]);

        // Create all required deadlines for the season
        $requiredCategories = [
            'hte_assessment_form',
            'student_verification',
            'student_assessment_form',
            'internship_placement',
            'archive_students'
        ];

        foreach ($requiredCategories as $category) {
            Deadline::factory()->create([
                'internship_season_id' => $activeSeason->id,
                'category' => $category,
                'status' => 'active',
                'end_date' => Carbon::now()->addDays(5),
            ]);
        }

        // Create a season that should be completed (past end date)
        $completedSeason = InternshipSeason::factory()->create([
            'name' => 'Past Season',
            'start_date' => Carbon::now()->subDays(20),
            'end_date' => Carbon::now()->subDays(5),
            'status' => 'active',
        ]);

        // Run the command
        $this->artisan(UpdateSeasonStatusesCommand::class)
            ->assertExitCode(0);

        // Assert that the active season was activated
        $activeSeason->refresh();
        $this->assertEquals('active', $activeSeason->status);

        // Assert that the completed season was deactivated
        $completedSeason->refresh();
        $this->assertEquals('completed', $completedSeason->status);
    }

    public function test_command_handles_dry_run_mode()
    {
        // Create a season that needs status update
        $season = InternshipSeason::factory()->create([
            'name' => 'Test Season',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
            'status' => 'inactive',
        ]);

        // Run the command in dry-run mode
        $this->artisan(UpdateSeasonStatusesCommand::class, ['--dry-run' => true])
            ->assertExitCode(0);

        // Assert that no changes were made
        $season->refresh();
        $this->assertEquals('inactive', $season->status);
    }

    public function test_command_does_not_activate_season_without_deadlines()
    {
        // Create a season within date range but without deadlines
        $season = InternshipSeason::factory()->create([
            'name' => 'Test Season',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
            'status' => 'inactive',
        ]);

        // Run the command
        $this->artisan(UpdateSeasonStatusesCommand::class)
            ->assertExitCode(0);

        // Assert that the season was not activated
        $season->refresh();
        $this->assertEquals('inactive', $season->status);
    }
}
