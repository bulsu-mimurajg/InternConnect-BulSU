<?php

namespace App\Services;

use App\Models\InternshipSeason;
use App\Models\Student;
use App\Models\Deadline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InternshipSeasonService
{
    /**
     * Create a new internship season
     */
    public function createSeason(string $name, Carbon $startDate, Carbon $endDate): InternshipSeason
    {
        return DB::transaction(function () use ($name, $startDate, $endDate) {
            // Validate dates
            if ($startDate->isAfter($endDate)) {
                throw new \InvalidArgumentException('Start date cannot be after end date.');
            }

            // Create the season
            $season = InternshipSeason::create([
                'name' => $name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'inactive',
            ]);

            Log::info('Created new internship season', [
                'season_id' => $season->id,
                'name' => $season->name,
                'start_date' => $season->start_date,
                'end_date' => $season->end_date,
            ]);

            return $season;
        });
    }

    /**
     * Check if a season can be activated (all 5 deadline categories must exist)
     */
    public function canActivateSeason(InternshipSeason $season): array
    {
        // Check if all 5 deadline categories exist for this season
        $requiredCategories = [
            'hte_assessment_form',
            'student_verification', 
            'student_assessment_form',
            'internship_placement',
            'archive_students'
        ];
        
        $existingCategories = $season->deadlines()->pluck('category')->toArray();
        $missingCategories = array_diff($requiredCategories, $existingCategories);
        
        if (!empty($missingCategories)) {
            return [
                'can_activate' => false,
                'message' => 'All 5 deadline categories must be created before activating the season.',
                'missing_categories' => $missingCategories
            ];
        }
        
        return ['can_activate' => true];
    }

    /**
     * Activate a season with validation
     */
    public function activateSeason(InternshipSeason $season): bool
    {
        // Check if another season is active
        $activeSeason = InternshipSeason::where('status', 'active')
            ->where('id', '!=', $season->id)
            ->first();
        
        if ($activeSeason) {
            throw new \Exception('Another season is currently active. Please deactivate it first.');
        }
        
        // Validate all categories exist
        $validation = $this->canActivateSeason($season);
        if (!$validation['can_activate']) {
            throw new \Exception($validation['message']);
        }
        
        $season->update(['status' => 'active']);
        return true;
    }

    /**
     * Deactivate a season with confirmation
     */
    public function deactivateSeason(InternshipSeason $season, bool $confirmed = false): bool
    {
        if (!$confirmed) {
            throw new \Exception('Deactivation requires confirmation.');
        }
        
        return DB::transaction(function () use ($season) {
            // Get count of active deadlines before expiring them
            $activeDeadlinesCount = $season->deadlines()->where('status', 'active')->count();
            
            // Expire all active deadlines in the season
            $expiredCount = $season->deadlines()->where('status', 'active')->update(['status' => 'expired']);
            
            // Mark season as completed
            $season->update(['status' => 'completed']);
            
            Log::info('Deactivated internship season and expired deadlines', [
                'season_id' => $season->id,
                'season_name' => $season->name,
                'active_deadlines_count' => $activeDeadlinesCount,
                'expired_deadlines_count' => $expiredCount,
            ]);
            
            return true;
        });
    }

    /**
     * Complete a season
     */
    public function completeSeason(int $seasonId): InternshipSeason
    {
        return DB::transaction(function () use ($seasonId) {
            $season = InternshipSeason::findOrFail($seasonId);

            // Check if season can be completed (no active deadlines)
            if ($season->hasActiveDeadlines()) {
                throw new \Exception('Cannot complete season with active deadlines.');
            }

            $season->update(['status' => 'completed']);

            Log::info('Completed internship season', [
                'season_id' => $season->id,
                'name' => $season->name,
            ]);

            return $season->fresh();
        });
    }

    /**
     * Archive all students in a season
     */
    public function archiveSeasonStudents(int $seasonId): int
    {
        return DB::transaction(function () use ($seasonId) {
            $season = InternshipSeason::findOrFail($seasonId);

            // Archive all active students in this season
            $archivedCount = $season->students()
                ->where('is_active', true)
                ->update(['is_active' => false]);

            Log::info('Archived students for season', [
                'season_id' => $season->id,
                'season_name' => $season->name,
                'archived_count' => $archivedCount,
            ]);

            return $archivedCount;
        });
    }

    /**
     * Validate season transition (ensure only one active season)
     */
    public function validateSeasonTransition(): void
    {
        $activeSeasons = InternshipSeason::where('status', 'active')->get();
        
        if ($activeSeasons->count() > 1) {
            throw new \Exception('Multiple active seasons detected. Only one season can be active at a time.');
        }
    }

    /**
     * Get the current active season
     */
    public function getActiveSeason(): ?InternshipSeason
    {
        return InternshipSeason::getActiveSeason();
    }

    /**
     * Check if a season can be archived
     */
    public function canArchiveSeason(int $seasonId): array
    {
        $season = InternshipSeason::findOrFail($seasonId);

        $canArchive = $season->canArchive();
        $activeDeadlines = $season->deadlines()
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->get();

        return [
            'can_archive' => $canArchive,
            'active_deadlines' => $activeDeadlines,
            'reason' => $canArchive ? null : 'Season has active deadlines that must expire first.',
        ];
    }

    /**
     * Get season statistics
     */
    public function getSeasonStats(int $seasonId): array
    {
        $season = InternshipSeason::findOrFail($seasonId);

        return [
            'season' => $season,
            'deadline_count' => $season->deadline_count,
            'active_deadline_count' => $season->active_deadline_count,
            'student_count' => $season->student_count,
            'active_student_count' => $season->active_student_count,
            'is_in_progress' => $season->isInProgress(),
            'has_ended' => $season->hasEnded(),
            'next_category' => $season->getNextCategoryToCreate(),
        ];
    }

    /**
     * Get all seasons with their statistics
     */
    public function getAllSeasonsWithStats(): \Illuminate\Database\Eloquent\Collection
    {
        return InternshipSeason::withCount([
            'deadlines',
            'students',
            'students as active_students_count' => function ($query) {
                $query->where('is_active', true);
            }
        ])->orderBy('created_at', 'desc')->get();
    }

    /**
     * Check if archive students deadline has expired for a season
     */
    public function hasArchiveDeadlineExpired(int $seasonId): bool
    {
        $season = InternshipSeason::findOrFail($seasonId);

        $archiveDeadline = $season->getActiveDeadlineForCategory('archive_students');

        if (!$archiveDeadline) {
            return false;
        }

        return $archiveDeadline->isExpired();
    }

    /**
     * Trigger automatic archiving when archive deadline expires
     */
    public function handleArchiveDeadlineExpiration(int $seasonId): void
    {
        if ($this->hasArchiveDeadlineExpired($seasonId)) {
            $this->archiveSeasonStudents($seasonId);
            $this->completeSeason($seasonId);

            Log::info('Automatically archived students and completed season due to expired archive deadline', [
                'season_id' => $seasonId,
            ]);
        }
    }
}
