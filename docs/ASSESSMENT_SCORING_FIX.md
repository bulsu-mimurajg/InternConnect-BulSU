# Assessment Scoring Fix - Percentage-Based Grading

## Date
October 29, 2025

## Problem
The student assessment was calculating scores using Likert scale averaging (1-5 scale) instead of quiz-style percentage grading. A student with only 4 correct answers out of 88 questions had a score of 37.40/5.00 (3.74 average), which is incorrect for a quiz.

## Root Cause
The scoring logic was treating all responses as Likert scale values (1-5) and averaging them:
```php
// OLD CODE (WRONG)
$meanScore = (array_sum($scores) / count($scores));
```

This meant:
- Correct answer = treated as value "1" or "2" (depending on answer ID)
- Averaged across all questions
- Result: High scores even with mostly wrong answers

## Solution

### New Scoring Formula
**Per Subcategory Percentage:**
```
Percentage = (Points Earned / Total Points) × 100
Scaled Score = (Percentage / 100) × 5
```

### Example Calculation

**Subcategory: General Programming Concepts**
- Total questions: 20
- Each question worth: 1 point
- Total possible points: 20
- Student got correct: 4 questions
- Points earned: 4

```
Percentage = (4 / 20) × 100 = 20%
Scaled Score = (20 / 100) × 5 = 1.00 / 5.00
```

**Much more accurate!**

## Implementation

### Updated Code
**File**: `app/Http/Controllers/AssessmentController.php` - `store()` method

**Before:**
```php
// Collect scores for mean calculation
if (!isset($subcategoryScores[$subcategory->id])) {
    $subcategoryScores[$subcategory->id] = [];
}
$subcategoryScores[$subcategory->id][] = $scoreValue;

// Compute mean scores
foreach ($subcategoryScores as $subcategoryId => $scores) {
    $meanScore = (array_sum($scores) / count($scores)); // WRONG!
    
    StudentScore::updateOrCreate([
        'student_id' => $student->id,
        'sub_category_id' => $subcategoryId,
    ], [
        'score' => $meanScore,
    ]);
}
```

**After:**
```php
// Collect points earned and total points per subcategory
if (!isset($subcategoryScores[$subcategory->id])) {
    $subcategoryScores[$subcategory->id] = [
        'points_earned' => 0,
        'total_points' => 0,
        'question_count' => 0
    ];
}

// Add points earned (based on correctness)
$subcategoryScores[$subcategory->id]['points_earned'] += $pointsEarned;
$subcategoryScores[$subcategory->id]['total_points'] += ($question->points ?? 1);
$subcategoryScores[$subcategory->id]['question_count']++;

// Compute percentage scores
foreach ($subcategoryScores as $subcategoryId => $scoreData) {
    // Calculate percentage
    $percentage = $scoreData['total_points'] > 0 
        ? ($scoreData['points_earned'] / $scoreData['total_points']) * 100 
        : 0;
    
    // Convert to 0-5 scale (for HTE matching compatibility)
    $scaledScore = ($percentage / 100) * 5;

    StudentScore::updateOrCreate([
        'student_id' => $student->id,
        'sub_category_id' => $subcategoryId,
    ], [
        'score' => $scaledScore,
    ]);
}
```

## Key Changes

### 1. Track Points, Not Values
- **Before**: Stored arbitrary score values (1-5)
- **After**: Tracks actual points earned vs total points

### 2. Calculate Percentage
- **Before**: Averaged arbitrary values
- **After**: `(points_earned / total_points) × 100`

### 3. Scale to 0-5
- Maintains compatibility with HTE matching system
- 0% = 0.00, 50% = 2.50, 100% = 5.00

### 4. Skip Empty Subcategories
- Only calculates scores for subcategories with questions
- No division by zero errors

## Impact

### Score Comparison

**Student with 4 correct answers out of 88 questions:**

| Metric | Old System (WRONG) | New System (CORRECT) |
|--------|-------------------|---------------------|
| Raw Score | 37.40 | ~2.27 |
| Average | 3.74 / 5.00 | ~0.23 / 5.00 |
| Percentage | 74.8% | 4.5% |
| Interpretation | "Good performance" | "Poor performance" |

**Much more accurate reflection of quiz performance!**

### For Students
- ✅ Scores now accurately reflect quiz performance
- ✅ Percentage-based grading is clear and fair
- ❌ Existing scores need to be recalculated (students need to retake)

### For HTE Matching
- ✅ Maintains 0-5 scale for compatibility
- ✅ More accurate skill representation
- ✅ Better matching with HTE requirements

## Migration Path

### For Existing Students
Students who already submitted assessments will have **incorrect scores** calculated with the old method.

**Options:**
1. **Reset and Retake** (Recommended):
   ```sql
   -- Delete old scores and quiz attempts for a student
   DELETE FROM student_score WHERE student_id = ?;
   DELETE FROM quiz_attempts WHERE student_id = ?;
   UPDATE students SET is_submit = 0 WHERE id = ?;
   ```
   
2. **Recalculate from Quiz Attempts**:
   - Create artisan command to recalculate scores
   - Use existing quiz_attempts data
   - Recompute with new formula

### For New Students
- ✅ Will get correct scores automatically
- ✅ No action needed

## Testing

### Test Case 1: Perfect Score
```
Questions: 10
Correct: 10
Expected: 100% = 5.00/5.00
```

### Test Case 2: Half Correct
```
Questions: 20
Correct: 10
Expected: 50% = 2.50/5.00
```

### Test Case 3: Poor Performance
```
Questions: 88
Correct: 4
Expected: 4.5% = 0.23/5.00
```

### Test Case 4: No Questions in Subcategory
```
Questions: 0
Expected: Subcategory skipped (not included in calculation)
```

## Logging

Added detailed logging for debugging:
```php
\Log::info('Subcategory score calculated', [
    'subcategory_id' => $subcategoryId,
    'points_earned' => $scoreData['points_earned'],
    'total_points' => $scoreData['total_points'],
    'percentage' => $percentage,
    'scaled_score' => $scaledScore
]);
```

Check logs to verify calculations are correct.

## Files Modified

1. `app/Http/Controllers/AssessmentController.php`
   - Updated `store()` method
   - Changed scoring calculation logic
   - Added percentage-based grading

## Technical Details

### Formula Components

**Points Earned:**
- Calculated from `is_correct` flag in quiz attempts
- `points_earned = is_correct ? question_points : 0`

**Total Points:**
- Sum of all `question.points` in subcategory
- Defaults to 1 point per question if not specified

**Percentage:**
- `(points_earned / total_points) × 100`
- Represents actual quiz performance

**Scaled Score (0-5):**
- `(percentage / 100) × 5`
- Maintains compatibility with HTE matching
- Used in compatibility score calculations

### Data Structure

**Old:**
```php
$subcategoryScores[$subcategoryId] = [5, 1, 3, 4, 2]; // Arbitrary values
```

**New:**
```php
$subcategoryScores[$subcategoryId] = [
    'points_earned' => 12,
    'total_points' => 20,
    'question_count' => 20
];
```

## Validation

To verify the fix works:

1. Submit a new assessment
2. Check logs for score calculation details
3. Verify `student_score` table has correct values
4. Compare with manual calculation:
   - Count correct answers per subcategory
   - Count total questions per subcategory
   - Calculate percentage
   - Verify scaled score (0-5)

## Related Issues

This fix also resolves:
- Inflated scores for poor performance
- Misleading assessment results
- Incorrect HTE matching based on scores
- Student confusion about grading system

