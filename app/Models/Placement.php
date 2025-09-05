<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Placement extends StudentMatch
{
    // This model now extends StudentMatch to maintain backward compatibility
    // while using the existing student_matches table structure

    protected $table = 'student_placements';

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
