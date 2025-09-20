<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_number',
        'first_name',
        'middle_name',
        'last_name',
        'phone',
        'section_id',
        'specialization',
        'address',
        'birth_date',
        'is_submit',
        'is_placed',
        'is_active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(StudentScore::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(StudentMatch::class);
    }

    /**
     * Get all compatibility scores for this student
     */
    public function compatibilityScores(): HasMany
    {
        return $this->hasMany(StudentMatch::class);
    }

    /**
     * Get actual placements (approved/rejected) for this student
     */
    public function placements(): HasMany
    {
        return $this->hasMany(StudentPlacement::class);
    }

    /**
     * Get the current active placement for this student
     */
    public function currentPlacement(): BelongsTo
    {
        return $this->belongsTo(StudentPlacement::class, 'id', 'student_id')
            ->where('status', 'approved');
    }

    /**
     * Get endorsements for this student
     */
    public function endorsements(): HasMany
    {
        return $this->hasMany(Endorsement::class);
    }
}
