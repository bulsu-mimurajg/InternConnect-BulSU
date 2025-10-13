<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class InternshipSeason extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get all deadlines for this season
     */
    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class);
    }

    /**
     * Get all students for this season
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Scope for active seasons
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed seasons
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for archived seasons
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Check if season is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if season has any active deadlines
     */
    public function hasActiveDeadlines(): bool
    {
        return $this->deadlines()
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->exists();
    }

    /**
     * Check if season can be archived (all deadlines expired)
     */
    public function canArchive(): bool
    {
        return !$this->hasActiveDeadlines();
    }

    /**
     * Archive all students in this season
     */
    public function archiveStudents(): int
    {
        return $this->students()
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Get the current active season
     */
    public static function getActiveSeason(): ?self
    {
        return self::where('status', 'active')->first();
    }

    /**
     * Ensure only one active season exists
     */
    public static function ensureOnlyOneActive(): void
    {
        $activeSeasons = self::where('status', 'active')->get();
        
        if ($activeSeasons->count() > 1) {
            throw new \Exception('Multiple active seasons detected. Only one season can be active at a time.');
        }
    }

    /**
     * Get season display name with status
     */
    public function getDisplayNameAttribute(): string
    {
        $statusBadge = match($this->status) {
            'active' => '🟢',
            'inactive' => '⚪',
            'completed' => '✅',
            'archived' => '📁',
            default => '❓'
        };
        
        return "{$statusBadge} {$this->name}";
    }

    /**
     * Get deadline count for this season
     */
    public function getDeadlineCountAttribute(): int
    {
        return $this->deadlines()->count();
    }

    /**
     * Get active deadline count for this season
     */
    public function getActiveDeadlineCountAttribute(): int
    {
        return $this->deadlines()
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->count();
    }

    /**
     * Get student count for this season
     */
    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }

    /**
     * Get active student count for this season
     */
    public function getActiveStudentCountAttribute(): int
    {
        return $this->students()->where('is_active', true)->count();
    }

    /**
     * Check if season is in progress (between start and end dates)
     */
    public function isInProgress(): bool
    {
        $now = Carbon::now();
        return $now->between($this->start_date, $this->end_date);
    }

    /**
     * Check if season has ended
     */
    public function hasEnded(): bool
    {
        return Carbon::now()->isAfter($this->end_date);
    }

    /**
     * Get the next category that should be created for this season
     */
    public function getNextCategoryToCreate(): ?string
    {
        $sequence = Deadline::getCategorySequence();
        
        foreach ($sequence as $category => $order) {
            if (!$this->hasDeadlineForCategory($category)) {
                return $category;
            }
        }
        
        return null; // All categories have deadlines
    }

    /**
     * Check if deadline exists for a specific category in this season
     */
    public function hasDeadlineForCategory(string $category): bool
    {
        return $this->deadlines()->where('category', $category)->exists();
    }

    /**
     * Get active deadline for a specific category in this season
     */
    public function getActiveDeadlineForCategory(string $category): ?Deadline
    {
        return $this->deadlines()
            ->where('category', $category)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->first();
    }
}