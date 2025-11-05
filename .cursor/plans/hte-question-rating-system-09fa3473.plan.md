<!-- 09fa3473-9890-474d-a86a-47b1ef108ab4 03168ad3-9f67-4a82-8e22-4e7774b9715d -->
# HTE Question Rating System Implementation

## Overview

Replace the current subcategory weight system with a question-based importance rating system. HTE users will rate each question's importance on a Likert scale (1-5), and these ratings will determine passing thresholds for students.

## Database Changes

### 1. Create New Migration: `question_importance_ratings` table

- Fields: `id`, `hte_id`, `internship_id`, `question_id`, `rating` (1-5), `timestamps`
- Foreign keys: `hte_id` → `hte.id`, `internship_id` → `internships.id`, `question_id` → `questions.id`
- Unique constraint: `(hte_id, internship_id, question_id)` to prevent duplicate ratings

### 2. Update Models

- Create `QuestionImportanceRating` model with relationships to HTE, Internship, and Question
- Add relationship methods to HTE, Internship, and Question models

## Backend Changes

### 3. Update HTEController

- Modify `submit()` method to:
- Accept `questionRatings` instead of `subcategoryWeights`
- Validate all questions are rated (1-5)
- Store question ratings in new table
- Calculate and optionally store computed subcategory/category percentages
- Update `getCategoriesForCriteria()` to return questions with existing ratings if any

### 4. Create Helper Methods

- `calculateSubcategoryPercentage()`: (sum of question ratings) / (num questions × 5) × 100
- `calculateCategoryPercentage()`: (sum of all question ratings in category) / (total questions in category × 5) × 100
- `mapPercentageToRating()`: Convert percentage to Likert equivalent (96-100=5, 90-95=4, 80-89=3, 75-79=2, <75=1)

### 5. Update MatchingService (if needed)

- May need to adapt compatibility calculation to use question ratings instead of subcategory weights
- Check if current weight-based matching can work with computed percentages

## Frontend Changes

### 6. Update Form Schema (`resources/js/components/form/hte/form.tsx`)

- Replace `subcategoryWeights` with `questionRatings` in FormSchema
- Validation: all questions must have ratings 1-5

### 7. Redesign Criteria Component (`resources/js/components/form/hte/criteria.tsx`)

- Remove subcategory weight inputs
- Add Likert scale rating UI for each question:
- Radio buttons or buttons for 1-5
- Labels: "Not Important" (1) → "Most Important" (5)
- Show question text clearly
- Display computed percentages in real-time:
- Subcategory percentage: (ratings sum / (num questions × 5)) × 100
- Category percentage: (all ratings sum / (total questions × 5)) × 100
- Show passing threshold (what students need to achieve)
- Show descriptive rating equivalent (Excellent, Very Good, Good, Fair, Poor)
- Validation: All questions must be rated before proceeding

### 8. Update UI Display

- Show question ratings in collapsible sections (category → subcategory → questions)
- Real-time calculation and display of:
- Individual question rating
- Subcategory percentage and equivalent rating
- Category percentage and equivalent rating
- Passing threshold indicator

### 9. Update Review & Submit Component

- Display question ratings instead of subcategory weights
- Show computed percentages and passing thresholds

## Validation & Business Logic

### 10. Form Validation

- All questions must be rated (1-5) before proceeding to next step
- Show clear error messages for unrated questions
- Highlight unrated questions in UI

### 11. Calculation Logic

- Subcategory % = (sum of question ratings) / (number of questions × 5) × 100
- Category % = (sum of all question ratings in category) / (total questions in category × 5) × 100
- Map percentages to ratings: 96-100=5, 90-95=4, 80-89=3, 75-79=2, <75=1

## Testing Considerations

- Test with different numbers of questions per subcategory
- Test with different category/subcategory structures
- Verify calculations match expected percentages
- Test validation prevents submission without all ratings
- Test real-time percentage updates as ratings change