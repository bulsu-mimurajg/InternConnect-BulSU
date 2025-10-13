<?php

namespace Tests\Feature\Commands;

use App\Console\Commands\UpdateDeadlineStatusesCommand;
use App\Models\Deadline;
use App\Models\InternshipSeason;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UpdateDeadlineStatusesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_updates_deadline_statuses_correctly()
    {
        // Create an active season
        $season = InternshipSeason::factory()->create([
            'name' => 'Test Season',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
            'status' => 'active',
        ]);

        // Create a deadline that should be active (within date range)
        $activeDeadline = Deadline::factory()->create([
            'title' => 'Active Deadline',
            'category' => 'hte_assessment_form',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
            'status' => 'inactive', // Currently inactive but should be active
            'internship_season_id' => $season->id,
        ]);

        // Create a deadline that should be inactive (before start date)
        $inactiveDeadline = Deadline::factory()->create([
            'title' => 'Inactive Deadline',
            'category' => 'student_verification',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'status' => 'active', // Currently active but should be inactive
            'internship_season_id' => $season->id,
        ]);

        // Create a deadline that should be expired (past end date)
        $expiredDeadline = Deadline::factory()->create([
            'title' => 'Expired Deadline',
            'category' => 'student_assessment_form',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(5),
            'status' => 'active', // Currently active but should be expired
            'internship_season_id' => $season->id,
        ]);

        // Run the command
        $this->artisan(UpdateDeadlineStatusesCommand::class)
            ->assertExitCode(0);

        // Assert that the active deadline was activated
        $activeDeadline->refresh();
        $this->assertEquals('active', $activeDeadline->status);

        // Assert that the inactive deadline was deactivated
        $inactiveDeadline->refresh();
        $this->assertEquals('inactive', $inactiveDeadline->status);

        // Assert that the expired deadline was expired
        $expiredDeadline->refresh();
        $this->assertEquals('expired', $expiredDeadline->status);
    }

    public function test_command_handles_dry_run_mode()
    {
        // Create a season and deadline
        $season = InternshipSeason::factory()->create([
            'status' => 'active',
        ]);

        $deadline = Deadline::factory()->create([
            'title' => 'Test Deadline',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
            'status' => 'inactive',
            'internship_season_id' => $season->id,
        ]);

        // Run the command in dry-run mode
        $this->artisan(UpdateDeadlineStatusesCommand::class, ['--dry-run' => true])
            ->assertExitCode(0);

        // Assert that no changes were made
        $deadline->refresh();
        $this->assertEquals('inactive', $deadline->status);
    }

    public function test_deadline_model_automatic_status_methods()
    {
        $season = InternshipSeason::factory()->create(['status' => 'active']);
        
        // Test inactive deadline
        $inactiveDeadline = Deadline::factory()->create([
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'status' => 'inactive',
            'internship_season_id' => $season->id,
        ]);

        $this->assertEquals('inactive', $inactiveDeadline->getAutomaticStatus());
        $this->assertTrue($inactiveDeadline->isInactive());
        $this->assertFalse($inactiveDeadline->isActive());
        $this->assertFalse($inactiveDeadline->isExpired());

        // Test active deadline
        $activeDeadline = Deadline::factory()->create([
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
            'status' => 'active',
            'internship_season_id' => $season->id,
        ]);

        $this->assertEquals('active', $activeDeadline->getAutomaticStatus());
        $this->assertTrue($activeDeadline->isActive());
        $this->assertFalse($activeDeadline->isInactive());
        $this->assertFalse($activeDeadline->isExpired());

        // Test expired deadline
        $expiredDeadline = Deadline::factory()->create([
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(5),
            'status' => 'expired',
            'internship_season_id' => $season->id,
        ]);

        $this->assertEquals('expired', $expiredDeadline->getAutomaticStatus());
        $this->assertTrue($expiredDeadline->isExpired());
        $this->assertFalse($expiredDeadline->isActive());
        $this->assertFalse($expiredDeadline->isInactive());
    }

    public function test_deadline_status_transition_reasons()
    {
        $season = InternshipSeason::factory()->create(['status' => 'active']);
        
        // Test inactive reason
        $inactiveDeadline = Deadline::factory()->create([
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'internship_season_id' => $season->id,
        ]);

        $this->assertEquals('Deadline has not started yet', $inactiveDeadline->getStatusTransitionReason());

        // Test active reason
        $activeDeadline = Deadline::factory()->create([
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
            'internship_season_id' => $season->id,
        ]);

        $this->assertEquals('Deadline is currently active', $activeDeadline->getStatusTransitionReason());

        // Test expired reason
        $expiredDeadline = Deadline::factory()->create([
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(5),
            'internship_season_id' => $season->id,
        ]);

        $this->assertEquals('Deadline has expired', $expiredDeadline->getStatusTransitionReason());
    }
}
