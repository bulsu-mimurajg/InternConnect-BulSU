<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endorsement extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'internship_id',
        'status',
        'compatibility_score',
        'notes',
        'endorsement_date',
    ];

    protected $casts = [
        'compatibility_score' => 'decimal:2',
        'endorsement_date' => 'datetime',
    ];

    /**
     * Get the student that owns the endorsement.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the internship that owns the endorsement.
     */
    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class);
    }

    /**
     * Get the compatibility score as a percentage.
     */
    public function getScorePercentageAttribute(): float
    {
        return round($this->compatibility_score, 2);
    }

    /**
     * Get the status label based on compatibility score.
     */
    public function getScoreStatusAttribute(): string
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
     * Scope to get endorsements for a specific student
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get endorsements for a specific internship
     */
    public function scopeForInternship($query, $internshipId)
    {
        return $query->where('internship_id', $internshipId);
    }

    /**
     * Scope to get endorsements with a specific status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get non-rejected endorsements (pending and endorsed)
     */
    public function scopeNotRejected($query)
    {
        return $query->whereIn('status', ['pending', 'endorsed']);
    }

    /**
     * Scope to get rejected endorsements
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the endorsement is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the endorsement is endorsed
     */
    public function isEndorsed(): bool
    {
        return $this->status === 'endorsed';
    }

    /**
     * Check if the endorsement is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}