<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AssessmentGradingCriteria extends Model
{
    use HasFactory;

    protected $table = 'assessment_grading_criteria';

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'min_score',
        'max_score',
        'grade_label',
        'grade_code',
        'grade_point',
        'description',
        'feedback_template',
        'color_code',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category this criteria belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the subcategory this criteria belongs to
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Scope to filter only active criteria
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to filter by subcategory
     */
    public function scopeForSubcategory(Builder $query, int $subcategoryId): Builder
    {
        return $query->where('subcategory_id', $subcategoryId);
    }

    /**
     * Scope to get global criteria (not specific to category/subcategory)
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('category_id')->whereNull('subcategory_id');
    }

    /**
     * Check if a score falls within this criteria's range
     */
    public function containsScore(float $score): bool
    {
        return $score >= $this->min_score && $score <= $this->max_score;
    }

    /**
     * Get the criteria for a given score
     */
    public static function getCriteriaForScore(float $score, ?int $categoryId = null, ?int $subcategoryId = null): ?self
    {
        $query = static::active()
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->orderBy('display_order');

        // Try to find specific criteria first
        if ($subcategoryId) {
            $criteria = (clone $query)->forSubcategory($subcategoryId)->first();
            if ($criteria) {
                return $criteria;
            }
        }

        if ($categoryId) {
            $criteria = (clone $query)->forCategory($categoryId)->first();
            if ($criteria) {
                return $criteria;
            }
        }

        // Fall back to global criteria
        return (clone $query)->global()->first();
    }

    /**
     * Get formatted feedback for a student
     */
    public function getFormattedFeedback(array $data = []): string
    {
        if (!$this->feedback_template) {
            return $this->description ?? "Grade: {$this->grade_label}";
        }

        $feedback = $this->feedback_template;

        // Replace placeholders
        foreach ($data as $key => $value) {
            $feedback = str_replace("{{$key}}", $value, $feedback);
        }

        return $feedback;
    }
}

