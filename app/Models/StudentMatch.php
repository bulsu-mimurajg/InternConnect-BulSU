<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'internship_id',
        'rank',
        'compatibility_score',
        'endorsement_status',
        'placement_status',
    ];

    protected $casts = [
        'rank' => 'integer',
        'compatibility_score' => 'decimal:2',
    ];

    /**
     * Get the student that owns the match.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the internship that owns the match.
     */
    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class);
    }

    /**
     * Get the match score as a percentage.
     */
    public function getMatchScoreAttribute(): float
    {
        return round($this->compatibility_score, 2);
    }

    /**
     * Get the status of the match.
     */
    public function getStatusAttribute(): string
    {
        if ($this->compatibility_score >= 80) {
            return 'Excellent';
        } elseif ($this->compatibility_score >= 70) {
            return 'Good';
        } elseif ($this->compatibility_score >= 60) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    /**
     * Scope to get matches for a specific student
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get matches for a specific internship
     */
    public function scopeForInternship($query, $internshipId)
    {
        return $query->where('internship_id', $internshipId);
    }

    /**
     * Scope to get matches above a certain score threshold
     */
    public function scopeAboveScore($query, $score)
    {
        return $query->where('compatibility_score', '>=', $score);
    }

    /**
     * Scope to get matches with a specific endorsement status
     */
    public function scopeWithEndorsementStatus($query, $status)
    {
        return $query->where('endorsement_status', $status);
    }

    /**
     * Scope to get matches with a specific placement status
     */
    public function scopeWithPlacementStatus($query, $status)
    {
        return $query->where('placement_status', $status);
    }

    /**
     * Scope to get non-rejected endorsements (pending and endorsed)
     */
    public function scopeNotRejectedEndorsement($query)
    {
        return $query->whereIn('endorsement_status', ['pending', 'endorsed']);
    }

    /**
     * Scope to get rejected endorsements
     */
    public function scopeRejectedEndorsement($query)
    {
        return $query->where('endorsement_status', 'rejected');
    }

    /**
     * Scope to get non-rejected placements (pending and approved)
     */
    public function scopeNotRejectedPlacement($query)
    {
        return $query->whereIn('placement_status', ['pending', 'approved']);
    }

    /**
     * Scope to get rejected placements
     */
    public function scopeRejectedPlacement($query)
    {
        return $query->where('placement_status', 'rejected');
    }

    /**
     * Check if the endorsement is rejected
     */
    public function isEndorsementRejected(): bool
    {
        return $this->endorsement_status === 'rejected';
    }

    /**
     * Check if the endorsement is endorsed
     */
    public function isEndorsementEndorsed(): bool
    {
        return $this->endorsement_status === 'endorsed';
    }

    /**
     * Check if the endorsement is pending
     */
    public function isEndorsementPending(): bool
    {
        return $this->endorsement_status === 'pending';
    }

    /**
     * Check if the placement is rejected
     */
    public function isPlacementRejected(): bool
    {
        return $this->placement_status === 'rejected';
    }

    /**
     * Check if the placement is approved
     */
    public function isPlacementApproved(): bool
    {
        return $this->placement_status === 'approved';
    }

    /**
     * Check if the placement is pending
     */
    public function isPlacementPending(): bool
    {
        return $this->placement_status === 'pending';
    }

    /**
     * Scope to get top N matches for a student
     */
    public function scopeTopMatches($query, $studentId, $limit = 5)
    {
        return $query->where('student_id', $studentId)
                    ->orderBy('rank')
                    ->limit($limit);
    }
}
