<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Deadline extends Model
{
    protected $fillable = [
        'title',
        'category',
        'start_date',
        'end_date',
        'status',
        'internship_season_id',
    ];

    public $timestamps = true;

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the internship season that owns this deadline
     */
    public function internshipSeason(): BelongsTo
    {
        return $this->belongsTo(InternshipSeason::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($deadline) {
            // Store the original status to detect changes
            $originalStatus = $deadline->getOriginal('status');
            
            // Automatically set status based on dates
            $now = Carbon::now();
            if ($deadline->end_date <= $now) {
                $deadline->status = 'expired';
            } elseif ($deadline->start_date > $now) {
                $deadline->status = 'inactive';
            } else {
                $deadline->status = 'active';
            }
        });

        static::saved(function ($deadline) {
            // Check if this is a student assessment form deadline that just expired
            $originalStatus = $deadline->getOriginal('status');
            if ($deadline->category === 'student_assessment_form' && 
                $originalStatus !== 'expired' && 
                $deadline->status === 'expired') {
                
                \Illuminate\Support\Facades\Log::info('Student assessment form deadline expired. Triggering match recalculation...');
                $deadline->triggerMatchRecalculation();
            }
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date > Carbon::now();
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive' || $this->start_date > Carbon::now();
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->end_date <= Carbon::now();
    }

    /**
     * Get the automatic status based on current date
     */
    public function getAutomaticStatus(): string
    {
        $now = Carbon::now();
        
        if ($now->isAfter($this->end_date)) {
            return 'expired';
        } elseif ($now->isBefore($this->start_date)) {
            return 'inactive';
        } else {
            return 'active';
        }
    }

    /**
     * Check if the current status matches what it should be automatically
     */
    public function isStatusCorrect(): bool
    {
        return $this->status === $this->getAutomaticStatus();
    }

    /**
     * Get status transition reason
     */
    public function getStatusTransitionReason(): string
    {
        $now = Carbon::now();
        
        if ($now->isAfter($this->end_date)) {
            return 'Deadline has expired';
        } elseif ($now->isBefore($this->start_date)) {
            return 'Deadline has not started yet';
        } else {
            return 'Deadline is currently active';
        }
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayName(): string
    {
        return match($this->category) {
            'student_verification' => 'Student Verification',
            'student_assessment_form' => 'Student Assessment Form',
            'hte_assessment_form' => 'HTE Assessment Form',
            'internship_placement' => 'Internship Placement (SIP Endorsement & HTE Placement)',
            'archive_students' => 'Archive Students',
            // Legacy support for old categories
            'sip_endorsement' => 'SIP Endorsement',
            'student_placements_by_hte' => 'Student Placements by HTE',
            default => $this->category,
        };
    }

    /**
     * Check if deadline exists for a specific category (for sequence validation)
     * This checks if any deadline exists for the category, regardless of current status
     * Now checks within the active season only
     */
    public static function hasDeadlineForCategory(string $category): bool
    {
        $activeSeason = InternshipSeason::getActiveSeason();
        if (!$activeSeason) {
            return false; // No active season, so no deadlines can exist
        }
        
        return self::where('category', $category)
            ->where('internship_season_id', $activeSeason->id)
            ->exists();
    }

    /**
     * Check if deadline is currently active for a specific category
     * Now checks within the active season only
     */
    public static function isActiveForCategory(string $category): bool
    {
        $activeSeason = InternshipSeason::getActiveSeason();
        if (!$activeSeason) {
            return false; // No active season, so no deadlines can be active
        }
        
        return self::where('category', $category)
            ->where('internship_season_id', $activeSeason->id)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->where('start_date', '<=', Carbon::now())
            ->exists();
    }

    /**
     * Check if deadline is currently inactive for a specific category
     * Now checks within the active season only
     */
    public static function isInactiveForCategory(string $category): bool
    {
        $activeSeason = InternshipSeason::getActiveSeason();
        if (!$activeSeason) {
            return false; // No active season, so no deadlines can be inactive
        }
        
        return self::where('category', $category)
            ->where('internship_season_id', $activeSeason->id)
            ->where(function($query) {
                $query->where('status', 'inactive')
                      ->orWhere('start_date', '>', Carbon::now());
            })
            ->exists();
    }

    /**
     * Get active deadline for a specific category
     * Now checks within the active season only
     */
    public static function getActiveForCategory(string $category): ?self
    {
        $activeSeason = InternshipSeason::getActiveSeason();
        if (!$activeSeason) {
            return null; // No active season, so no deadlines can be active
        }
        
        return self::where('category', $category)
            ->where('internship_season_id', $activeSeason->id)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->where('start_date', '<=', Carbon::now())
            ->first();
    }

    /**
     * Get inactive deadline for a specific category
     * Now checks within the active season only
     */
    public static function getInactiveForCategory(string $category): ?self
    {
        $activeSeason = InternshipSeason::getActiveSeason();
        if (!$activeSeason) {
            return null; // No active season, so no deadlines can be inactive
        }
        
        return self::where('category', $category)
            ->where('internship_season_id', $activeSeason->id)
            ->where(function($query) {
                $query->where('status', 'inactive')
                      ->orWhere('start_date', '>', Carbon::now());
            })
            ->first();
    }

    /**
     * Get all expired deadlines
     * Now filters by active season only
     */
    public static function getExpired(): \Illuminate\Database\Eloquent\Collection
    {
        $activeSeason = InternshipSeason::getActiveSeason();
        if (!$activeSeason) {
            return self::whereRaw('1 = 0')->get(); // Return empty Eloquent collection
        }
        
        return self::where('internship_season_id', $activeSeason->id)
            ->where(function($query) {
                $query->where('status', 'expired')
                    ->orWhere('end_date', '<=', Carbon::now());
            })
            ->orderBy('end_date', 'desc')
            ->get();
    }

    /**
     * Get all active deadlines
     * Now filters by active season only
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        $activeSeason = InternshipSeason::getActiveSeason();
        if (!$activeSeason) {
            return self::whereRaw('1 = 0')->get(); // Return empty Eloquent collection
        }
        
        return self::where('internship_season_id', $activeSeason->id)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->where('start_date', '<=', Carbon::now())
            ->orderBy('end_date', 'asc')
            ->get();
    }

    /**
     * Get all inactive deadlines
     * Now filters by active season only
     */
    public static function getInactive(): \Illuminate\Database\Eloquent\Collection
    {
        $activeSeason = InternshipSeason::getActiveSeason();
        if (!$activeSeason) {
            return self::whereRaw('1 = 0')->get(); // Return empty Eloquent collection
        }
        
        return self::where('internship_season_id', $activeSeason->id)
            ->where(function($query) {
                $query->where('status', 'inactive')
                      ->orWhere('start_date', '>', Carbon::now());
            })
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Get all deadlines for the active season regardless of status
     * This includes active, inactive, and expired deadlines
     */
    public static function getAllForActiveSeason(): \Illuminate\Database\Eloquent\Collection
    {
        $activeSeason = InternshipSeason::getActiveSeason();
        if (!$activeSeason) {
            return self::whereRaw('1 = 0')->get(); // Return empty Eloquent collection
        }
        
        return self::where('internship_season_id', $activeSeason->id)
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Define the chronological order of deadline categories
     */
    public static function getCategorySequence(): array
    {
        return [
            'hte_assessment_form' => 1,
            'student_verification' => 2,
            'student_assessment_form' => 3,
            'internship_placement' => 4,
            'archive_students' => 5,
        ];
    }

    /**
     * Get the sequence order for a category
     */
    public static function getCategoryOrder(string $category): int
    {
        $sequence = self::getCategorySequence();
        return $sequence[$category] ?? 999; // Default to high number for unknown categories
    }

    /**
     * Check if a category can be created based on chronological sequence
     */
    public static function canCreateCategory(string $category): array
    {
        $sequence = self::getCategorySequence();
        $currentOrder = $sequence[$category] ?? 999;
        
        // Check if any previous categories in sequence don't have deadlines created
        $missingCategories = [];
        foreach ($sequence as $cat => $order) {
            if ($order < $currentOrder && !self::hasDeadlineForCategory($cat)) {
                $missingCategories[] = $cat;
            }
        }

        return [
            'can_create' => empty($missingCategories),
            'missing_categories' => $missingCategories,
            'current_order' => $currentOrder,
        ];
    }

    /**
     * Get the next category that should be created
     */
    public static function getNextCategoryToCreate(): ?string
    {
        $sequence = self::getCategorySequence();
        
        foreach ($sequence as $category => $order) {
            if (!self::hasDeadlineForCategory($category)) {
                return $category;
            }
        }
        
        return null; // All categories have deadlines
    }

    /**
     * Check and update deadline statuses based on current date
     */
    public static function checkAndUpdateStatuses(): array
    {
        $results = [
            'activated' => [],
            'deactivated' => [],
            'expired' => [],
            'errors' => []
        ];

        try {
            \DB::transaction(function () use (&$results) {
                // Get all deadlines that need status updates
                $deadlines = self::whereIn('status', ['inactive', 'active'])->get();
                
                foreach ($deadlines as $deadline) {
                    $automaticStatus = $deadline->getAutomaticStatus();
                    
                    // Skip if status is already correct
                    if ($deadline->status === $automaticStatus) {
                        continue;
                    }
                    
                    try {
                        $oldStatus = $deadline->status;
                        $deadline->update(['status' => $automaticStatus]);
                        
                        if ($automaticStatus === 'active' && $oldStatus === 'inactive') {
                            $results['activated'][] = [
                                'id' => $deadline->id,
                                'title' => $deadline->title,
                                'category' => $deadline->category,
                                'reason' => $deadline->getStatusTransitionReason()
                            ];
                        } elseif ($automaticStatus === 'inactive' && $oldStatus === 'active') {
                            $results['deactivated'][] = [
                                'id' => $deadline->id,
                                'title' => $deadline->title,
                                'category' => $deadline->category,
                                'reason' => $deadline->getStatusTransitionReason()
                            ];
                        } elseif ($automaticStatus === 'expired') {
                            $results['expired'][] = [
                                'id' => $deadline->id,
                                'title' => $deadline->title,
                                'category' => $deadline->category,
                                'reason' => $deadline->getStatusTransitionReason()
                            ];
                        }
                    } catch (\Exception $e) {
                        $results['errors'][] = [
                            'deadline_id' => $deadline->id,
                            'deadline_title' => $deadline->title,
                            'error' => $e->getMessage()
                        ];
                    }
                }
            });
            
        } catch (\Exception $e) {
            $results['errors'][] = [
                'error' => 'Failed to check deadline statuses: ' . $e->getMessage()
            ];
        }
        
        return $results;
    }


    /**
     * Check if a category can be created based on chronological sequence for a specific season
     */
    public static function canCreateCategoryForSeason(string $category, int $seasonId): array
    {
        $sequence = self::getCategorySequence();
        $currentOrder = $sequence[$category] ?? 999;
        
        // Check if any previous categories in sequence don't have deadlines created in this season
        $missingCategories = [];
        foreach ($sequence as $cat => $order) {
            if ($order < $currentOrder && !self::hasDeadlineForCategoryInSeason($cat, $seasonId)) {
                $missingCategories[] = $cat;
            }
        }

        return [
            'can_create' => empty($missingCategories),
            'missing_categories' => $missingCategories,
            'current_order' => $currentOrder,
        ];
    }

    /**
     * Validate sequence for a specific season
     */
    public static function validateSequenceForSeason(string $category, int $seasonId): array
    {
        $validation = self::canCreateCategoryForSeason($category, $seasonId);
        
        if (!$validation['can_create']) {
            $missingNames = array_map(function($cat) {
                return (new self(['category' => $cat]))->getCategoryDisplayName();
            }, $validation['missing_categories']);
            
            return [
                'valid' => false,
                'message' => 'Cannot create this deadline yet. Please create deadlines in chronological order. Missing: ' . implode(', ', $missingNames)
            ];
        }
        
        return ['valid' => true];
    }

    /**
     * Validate chronological sequence before creating deadline
     */
    public static function validateSequence(string $category): array
    {
        $validation = self::canCreateCategory($category);
        
        if (!$validation['can_create']) {
            $missingDisplayNames = array_map(function($cat) {
                return (new self(['category' => $cat]))->getCategoryDisplayName();
            }, $validation['missing_categories']);
            
            return [
                'valid' => false,
                'message' => 'Cannot create ' . (new self(['category' => $category]))->getCategoryDisplayName() . 
                           ' deadline. Please create deadlines in chronological order. Missing: ' . 
                           implode(', ', $missingDisplayNames),
                'missing_categories' => $validation['missing_categories'],
            ];
        }
        
        return ['valid' => true];
    }

    /**
     * Check if this deadline is for archiving students
     */
    public function isArchiveStudentsDeadline(): bool
    {
        return $this->category === 'archive_students';
    }

    /**
     * Check if deadline exists for a specific category within a season
     */
    public static function hasDeadlineForCategoryInSeason(string $category, int $seasonId): bool
    {
        return self::where('category', $category)
            ->where('internship_season_id', $seasonId)
            ->exists();
    }

    /**
     * Check if deadline is currently active for a specific category within a season
     */
    public static function isActiveForCategoryInSeason(string $category, int $seasonId): bool
    {
        return self::where('category', $category)
            ->where('internship_season_id', $seasonId)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->exists();
    }

    /**
     * Get active deadline for a specific category within a season
     */
    public static function getActiveForCategoryInSeason(string $category, int $seasonId): ?self
    {
        return self::where('category', $category)
            ->where('internship_season_id', $seasonId)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->first();
    }

    /**
     * Trigger student match recalculation when assessment deadline expires
     */
    public function triggerMatchRecalculation(): void
    {
        try {
            $matchingService = new \App\Services\MatchingService();
            
            // Get all active students who have submitted assessments
            $students = \App\Models\Student::where('is_active', true)
                ->where('is_submit', true)
                ->get();
            
            if ($students->isEmpty()) {
                \Illuminate\Support\Facades\Log::info('No students with submitted assessments found for match recalculation');
                return;
            }
            
            $recalculatedCount = 0;
            
            foreach ($students as $student) {
                try {
                    $matchingService->calculateAndStoreCompatibilityScores($student);
                    $recalculatedCount++;
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to recalculate matches for student after assessment deadline', [
                        'student_id' => $student->id,
                        'deadline_id' => $this->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            \Illuminate\Support\Facades\Log::info('Recalculated student matches after assessment deadline expiration', [
                'deadline_id' => $this->id,
                'deadline_title' => $this->title,
                'recalculated_count' => $recalculatedCount,
                'total_eligible_students' => $students->count()
            ]);
            
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to trigger match recalculation after assessment deadline', [
                'deadline_id' => $this->id,
                'deadline_title' => $this->title,
                'error' => $e->getMessage()
            ]);
        }
    }
}
