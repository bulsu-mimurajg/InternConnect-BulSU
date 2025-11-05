<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionImportanceRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'hte_id',
        'internship_id',
        'question_id',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the HTE that owns this rating.
     */
    public function hte(): BelongsTo
    {
        return $this->belongsTo(HTE::class, 'hte_id');
    }

    /**
     * Get the internship that owns this rating.
     */
    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class, 'internship_id');
    }

    /**
     * Get the question that this rating belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
