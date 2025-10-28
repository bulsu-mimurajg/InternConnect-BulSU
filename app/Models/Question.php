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
        'code_snippet',
        'question_type',
        'points',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'decimal:2',
    ];

    /**
     * Get the subcategory that owns this question.
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }

    /**
     * Get all answers for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('display_order');
    }

    /**
     * Get the correct answer(s) for this question.
     */
    public function correctAnswers(): HasMany
    {
        return $this->hasMany(Answer::class)->where('is_correct', true);
    }

    /**
     * Get all quiz attempts for this question.
     */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Scope to get only active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get questions by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('question_type', $type);
    }

    /**
     * Check if this question requires manual grading.
     */
    public function requiresManualGrading(): bool
    {
        return in_array($this->question_type, ['essay', 'enumeration']);
    }

    /**
     * Check if this question can be auto-graded.
     */
    public function canBeAutoGraded(): bool
    {
        return in_array($this->question_type, ['multiple_choice', 'true_false', 'identification']);
    }
}
