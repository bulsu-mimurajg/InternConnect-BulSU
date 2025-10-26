<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'question_id',
        'selected_answer_id',
        'text_response',
        'is_correct',
        'points_earned',
        'points_possible',
        'graded_by',
        'graded_at',
        'feedback',
        'submitted_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_earned' => 'decimal:2',
        'points_possible' => 'decimal:2',
        'graded_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the student that made this quiz attempt.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the question for this quiz attempt.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the selected answer for this quiz attempt.
     */
    public function selectedAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'selected_answer_id');
    }

    /**
     * Get the user who graded this attempt.
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Scope to get only submitted attempts.
     */
    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    /**
     * Scope to get only graded attempts.
     */
    public function scopeGraded($query)
    {
        return $query->whereNotNull('graded_at');
    }

    /**
     * Scope to get only correct attempts.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope to get only incorrect attempts.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope to get attempts for a specific student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Check if this attempt needs manual grading.
     */
    public function needsGrading(): bool
    {
        return $this->is_correct === null && $this->submitted_at !== null;
    }

    /**
     * Auto-grade the attempt for objective questions.
     */
    public function autoGrade(): void
    {
        if ($this->selectedAnswer && in_array($this->question->question_type, ['multiple_choice', 'true_false', 'identification'])) {
            $this->is_correct = $this->selectedAnswer->is_correct;
            $this->points_earned = $this->is_correct ? $this->points_possible : 0;
            $this->save();
        }
    }
}
