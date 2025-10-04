<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Internship extends Model
{
    use HasFactory;

    protected $fillable = [
        'hte_id',
        'position_title',
        'department',
        'placement_description',
        'slot_count',
        'is_active',
    ];

    protected $casts = [
        'slot_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the HTE that owns the internship.
     */
    public function hte(): BelongsTo
    {
        return $this->belongsTo(HTE::class, 'hte_id', 'id');
    }



    /**
     * Get the subcategory weights for this internship.
     */
    public function subcategoryWeights(): HasMany
    {
        return $this->hasMany(SubcategoryWeight::class);
    }

    /**
     * Get all compatibility scores for this internship
     */
    public function compatibilityScores(): HasMany
    {
        return $this->hasMany(StudentMatch::class);
    }

    /**
     * Get actual student placements (approved/rejected) for this internship
     */
    public function studentPlacements(): HasMany
    {
        return $this->hasMany(StudentPlacement::class);
    }

    /**
     * Get the placements for this internship.
     */
    public function placements(): HasMany
    {
        return $this->hasMany(Placement::class);
    }

    /**
     * Scope a query to only include active internships.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include internships with available slots.
     */
    public function scopeWithAvailableSlots($query)
    {
        return $query->where('slot_count', '>', 0);
    }

    /**
     * Get endorsements for this internship
     */
    public function endorsements(): HasMany
    {
        return $this->hasMany(Endorsement::class);
    }

    /**
     * Get the number of available slots for this internship
     */
    public function getAvailableSlotsAttribute(): int
    {
        $filledSlots = $this->studentPlacements()
            ->where('status', 'approved')
            ->count();
        
        return max(0, $this->slot_count - $filledSlots);
    }

    /**
     * Get the number of filled slots for this internship
     */
    public function getFilledSlotsAttribute(): int
    {
        return $this->studentPlacements()
            ->where('status', 'approved')
            ->count();
    }
}
