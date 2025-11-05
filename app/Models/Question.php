<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';

    protected $fillable = [
        'subcategory_id',
        'question',
        'is_active',
        'question_type',
    ];

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class, 'question_id');
    }

    /**
     * Get the importance ratings for this question.
     */
    public function importanceRatings(): HasMany
    {
        return $this->hasMany(QuestionImportanceRating::class, 'question_id');
    }
}
