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
            
            // Check if internship placement deadline exists (regardless of status)
            $hasPlacementDeadline = $season->deadlines()
                ->where('category', 'internship_placement')
                ->exists();
            
            // Always trigger automatic placement when deactivating (admin override)
            // This allows admins to force placement processing regardless of deadline timing
            if ($hasPlacementDeadline) {
                $placementDeadlineStatus = $season->deadlines()
                    ->where('category', 'internship_placement')
                    ->value('status');
                
                Log::info('Triggering automatic placement on season deactivation (admin override)', [
                    'season_id' => $season->id,
                    'placement_deadline_status' => $placementDeadlineStatus,
                ]);
                
                $this->triggerAutomaticPlacementForSeason($season);
            } else {
                Log::warning('No internship placement deadline found for season, skipping automatic placement', [
                    'season_id' => $season->id,
                ]);
            }
            
            // THEN expire all deadlines in the season (active and inactive)
            $expiredCount = $season->deadlines()
                ->whereIn('status', ['active', 'inactive'])
                ->update(['status' => 'expired']);
            
            // Mark season as completed
            $season->update(['status' => 'completed']);
            
            Log::info('Deactivated internship season and expired deadlines', [
                'season_id' => $season->id,
                'season_name' => $season->name,
                'active_deadlines_count' => $activeDeadlinesCount,
                'expired_deadlines_count' => $expiredCount,
                'had_placement_deadline' => $hasPlacementDeadline,
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
        ])
        ->with(['deadlines' => function($query) {
            $query->select('id', 'category', 'status', 'internship_season_id');
        }])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($season) {
            // Get deadline statuses for each category
            $deadlineStatuses = [];
            $requiredCategories = [
                'hte_assessment_form',
                'student_verification', 
                'student_assessment_form',
                'internship_placement',
                'archive_students'
            ];
            
            foreach ($requiredCategories as $category) {
                $deadline = $season->deadlines->where('category', $category)->first();
                $deadlineStatuses[$category] = $deadline ? $deadline->status : 'inactive';
            }
            
            // Add deadline statuses to season data
            $season->deadline_statuses = $deadlineStatuses;
            
            return $season;
        });
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
    
    /**
     * Trigger automatic placement for a season
     */
    private function triggerAutomaticPlacementForSeason(InternshipSeason $season): void
    {
        // Tier 1: Place endorsed students
        $placementService = app(\App\Services\AutomaticPlacementService::class);
        $tier1Results = $placementService->processEndorsedStudentsPlacements();
        
        // Tier 2: Auto-endorse and place matched students
        $endorsementService = app(\App\Services\AutomaticEndorsementService::class);
        $tier2EndorseResults = $endorsementService->processMatchedStudentsEndorsement();
        $tier2PlaceResults = $placementService->processEndorsedStudentsPlacements();
        
        // Tier 3: Emergency placement
        $tier3Results = $placementService->processEmergencyPlacements();
        
        $totalPlaced = $tier1Results['placed_count'] + $tier2PlaceResults['placed_count'] + $tier3Results['emergency_placed_count'];
        
        Log::info('Automatic placement completed for season deactivation', [
            'season_id' => $season->id,
            'tier1_placed' => $tier1Results['placed_count'],
            'tier2_endorsed' => $tier2EndorseResults['endorsed_count'],
            'tier2_placed' => $tier2PlaceResults['placed_count'],
            'tier3_emergency' => $tier3Results['emergency_placed_count'],
            'total_placed' => $totalPlaced,
        ]);
    }

    /**
     * Check and update season statuses based on dates and deadlines
     */
    public function checkAndUpdateSeasonStatuses(): array
    {
        $results = [
            'activated' => [],
            'deactivated' => [],
            'errors' => []
        ];

        try {
            DB::transaction(function () use (&$results) {
                // First, update all deadline statuses
                $deadlineResults = \App\Models\Deadline::checkAndUpdateStatuses();
                
                // Get all seasons that need status updates
                $seasons = InternshipSeason::whereIn('status', ['inactive', 'active'])->get();
                
                foreach ($seasons as $season) {
                    $automaticStatus = $season->getAutomaticStatus();
                    
                    // Skip if status is already correct
                    if ($season->status === $automaticStatus) {
                        continue;
                    }
                    
                    try {
                        if ($automaticStatus === 'active' && $season->status === 'inactive') {
                            // Auto-activate season
                            $this->autoActivateSeason($season);
                            $results['activated'][] = [
                                'id' => $season->id,
                                'name' => $season->name,
                                'reason' => $season->getStatusTransitionReason()
                            ];
                        } elseif ($automaticStatus === 'completed' && $season->status === 'active') {
                            // Auto-deactivate season
                            $this->autoDeactivateSeason($season);
                            $results['deactivated'][] = [
                                'id' => $season->id,
                                'name' => $season->name,
                                'reason' => $season->getStatusTransitionReason()
                            ];
                        }
                    } catch (\Exception $e) {
                        $results['errors'][] = [
                            'season_id' => $season->id,
                            'season_name' => $season->name,
                            'error' => $e->getMessage()
                        ];
                        
                        Log::error('Failed to auto-update season status', [
                            'season_id' => $season->id,
                            'season_name' => $season->name,
                            'current_status' => $season->status,
                            'target_status' => $automaticStatus,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Add deadline results to the main results
                if (!empty($deadlineResults['activated'])) {
                    $results['deadline_activated'] = $deadlineResults['activated'];
                }
                if (!empty($deadlineResults['deactivated'])) {
                    $results['deadline_deactivated'] = $deadlineResults['deactivated'];
                }
                if (!empty($deadlineResults['expired'])) {
                    $results['deadline_expired'] = $deadlineResults['expired'];
                }
                if (!empty($deadlineResults['errors'])) {
                    $results['deadline_errors'] = $deadlineResults['errors'];
                }
            });
            
            Log::info('Season and deadline status check completed', $results);
            
        } catch (\Exception $e) {
            Log::error('Failed to check season statuses', [
                'error' => $e->getMessage()
            ]);
            
            $results['errors'][] = [
                'error' => 'Failed to check season statuses: ' . $e->getMessage()
            ];
        }
        
        return $results;
    }

    /**
     * Automatically activate a season
     */
    private function autoActivateSeason(InternshipSeason $season): void
    {
        // Check if another season is active
        $activeSeason = InternshipSeason::where('status', 'active')
            ->where('id', '!=', $season->id)
            ->first();
        
        if ($activeSeason) {
            // Auto-deactivate the current active season first
            $this->autoDeactivateSeason($activeSeason);
        }
        
        // Validate all categories exist
        if (!$season->hasAllRequiredDeadlines()) {
            throw new \Exception('Cannot auto-activate season: Missing required deadline categories');
        }
        
        $season->update(['status' => 'active']);
        
        Log::info('Automatically activated internship season', [
            'season_id' => $season->id,
            'season_name' => $season->name,
            'start_date' => $season->start_date,
            'end_date' => $season->end_date,
        ]);
    }

    /**
     * Automatically deactivate a season
     */
    private function autoDeactivateSeason(InternshipSeason $season): void
    {
        // Get count of active deadlines before expiring them
        $activeDeadlinesCount = $season->deadlines()->where('status', 'active')->count();
        
        // Check if internship placement deadline is active
        $hasActivePlacementDeadline = $season->deadlines()
            ->where('category', 'internship_placement')
            ->where('status', 'active')
            ->exists();
        
        // Expire all deadlines in the season (active and inactive)
        $expiredCount = $season->deadlines()
            ->whereIn('status', ['active', 'inactive'])
            ->update(['status' => 'expired']);
        
        // Mark season as completed
        $season->update(['status' => 'completed']);
        
        Log::info('Automatically deactivated internship season and expired deadlines', [
            'season_id' => $season->id,
            'season_name' => $season->name,
            'active_deadlines_count' => $activeDeadlinesCount,
            'expired_deadlines_count' => $expiredCount,
            'had_active_placement_deadline' => $hasActivePlacementDeadline,
        ]);
        
        // Trigger automatic placement if internship placement deadline was active
        if ($hasActivePlacementDeadline) {
            Log::info('Triggering automatic placement due to auto season deactivation');
            $this->triggerAutomaticPlacementForSeason($season);
        }
    }

}
