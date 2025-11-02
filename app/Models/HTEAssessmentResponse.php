<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HTEAssessmentResponse extends Model
{
    protected $fillable = [
        'hte_id',
        'internship_id',
        'question_id',
        'response',
    ];

    protected $casts = [
        'response' => 'integer',
    ];

    /**
     * Get the HTE that owns this response
     */
    public function hte(): BelongsTo
    {
        return $this->belongsTo(HTE::class);
    }

    /**
     * Get the internship this response is for
     */
    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class);
    }

    /**
     * Get the question this response answers
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the HTE question this response answers (if using hte_questions table)
     */
    public function hteQuestion(): BelongsTo
    {
        return $this->belongsTo(HTEQuestion::class, 'question_id');
    }
}

