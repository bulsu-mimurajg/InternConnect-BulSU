<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Deadline extends Model
{
    protected $fillable = [
        'title',
        'category',
        'start_date',
        'end_date',
        'status',
    ];

    public $timestamps = true;

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

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
            // Legacy support for old categories
            'sip_endorsement' => 'SIP Endorsement',
            'student_placements_by_hte' => 'Student Placements by HTE',
            default => $this->category,
        };
    }

    /**
     * Check if deadline exists for a specific category (for sequence validation)
     * This checks if any deadline exists for the category, regardless of current status
     */
    public static function hasDeadlineForCategory(string $category): bool
    {
        return self::where('category', $category)->exists();
    }

    /**
     * Check if deadline is currently active for a specific category
     */
    public static function isActiveForCategory(string $category): bool
    {
        return self::where('category', $category)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->exists();
    }

    /**
     * Get active deadline for a specific category
     */
    public static function getActiveForCategory(string $category): ?self
    {
        return self::where('category', $category)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->first();
    }

    /**
     * Get all expired deadlines
     */
    public static function getExpired(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('status', 'expired')
            ->orWhere('end_date', '<=', Carbon::now())
            ->orderBy('end_date', 'desc')
            ->get();
    }

    /**
     * Get all active deadlines
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('status', 'active')
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
}
