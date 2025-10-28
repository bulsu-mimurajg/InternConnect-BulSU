# HTE Assessment Fix - Loading Correct Questions

## Date
October 29, 2025

## Problem
The HTE Assessment page was displaying student quiz questions (multiple choice) instead of HTE criteria questions (Likert scale statements like "Interns are expected to understand fundamental programming logic and syntax").

## Root Cause
The `getCategoriesForCriteria()` method in `HTEController.php` was querying the wrong table:
- **Wrong**: Loading from `questions` table (student quiz questions)
- **Correct**: Should load from `hte_questions` table (HTE criteria questions)

## Solution

### 1. Updated HTEController.php
Changed the `getCategoriesForCriteria()` method to use `hteQuestions` relationship instead of `questions`:

**Before:**
```php
$categories = Category::with(['subCategories.questions' => function($query) {
    $query->where('is_active', true);
}])
```

**After:**
```php
$categories = Category::with(['subCategories.hteQuestions' => function($query) {
    $query->where('is_active', true);
}])
```

Also updated the mapping to use `hteQuestions`:
```php
'questions' => $subCategory->hteQuestions->map(function($question) {
    // ...
})
```

**Added filtering for empty subcategories:**
```php
->filter(function($subCategory) {
    // Only include subcategories that have at least one HTE question
    return $subCategory->hteQuestions->count() > 0;
})
```

**Added filtering for empty categories:**
```php
->filter(function($category) {
    // Only include categories that have at least one subcategory with questions
    return count($category['subCategories']) > 0;
})
```

### 2. Added hteQuestions Relationship to SubCategory Model
Added new relationship method to `SubCategory.php`:

```php
public function hteQuestions(): HasMany
{
    return $this->hasMany(HTEQuestion::class, 'subcategory_id');
}
```

## Database Tables

### questions (Student Quiz Questions)
- Contains multiple choice questions
- Has answers in `answers` table
- Used for student assessment
- Example: "Which of the following is a compiled language?"

### hte_questions (HTE Criteria Questions)
- Contains Likert scale criteria statements  
- No separate answers table (uses 1-5 scale)
- Used for HTE assessment
- Example: "Interns are expected to understand fundamental programming logic and syntax"

## Files Modified
1. `app/Http/Controllers/HTEController.php` - Fixed `getCategoriesForCriteria()` method
2. `app/Models/SubCategory.php` - Added `hteQuestions()` relationship

## Expected Behavior

### Before Fix
HTE sees questions like:
- "Which of the following is a relational database management system (RDBMS)?"
- Multiple choice answers: MySQL, MongoDB, PostgreSQL, Oracle

### After Fix  
HTE sees criteria like:
- "Interns are expected to understand fundamental programming logic and syntax"
- Likert scale: 1-Strongly Disagree, 2-Disagree, 3-Neutral, 4-Agree, 5-Strongly Agree

## Testing
1. Login as HTE user
2. Navigate to HTE Assessment form
3. Go to "Criteria" step
4. Verify questions show HTE criteria statements (not student quiz questions)
5. Verify Likert scale options (1-5) are displayed

## Impact
- ✅ HTE assessment now shows correct criteria questions
- ✅ Student assessment unaffected (still shows quiz questions)
- ✅ Both systems work independently with their own question sets
- ✅ Empty subcategories (with no HTE questions) are automatically hidden
- ✅ Categories with no subcategories are automatically hidden
- ✅ Cleaner UI - only relevant categories and subcategories are displayed

