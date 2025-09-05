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
        'status',
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
     * Scope to get matches with a specific status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get non-rejected matches (pending and approved)
     */
    public function scopeNotRejected($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    /**
     * Scope to get rejected matches
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the match is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the match is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the match is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
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
