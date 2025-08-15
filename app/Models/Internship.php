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
}
