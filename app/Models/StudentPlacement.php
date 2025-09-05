<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPlacement extends Model
{
    use HasFactory;
    
    protected $table = 'student_placements';

    protected $fillable = [
        'student_id',
        'internship_id',
        'status',
        'compatibility_score',
        'placement_date',
    ];

    protected $casts = [
        'placement_date' => 'datetime',
        'compatibility_score' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

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
}
