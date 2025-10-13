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
            // Automatically set status based on dates
            $now = Carbon::now();
            if ($deadline->end_date <= $now) {
                $deadline->status = 'expired';
            } else {
                $deadline->status = 'active';
            }
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date > Carbon::now();
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->end_date <= Carbon::now();
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
            ->orderBy('end_date', 'asc')
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
}
