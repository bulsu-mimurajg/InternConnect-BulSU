# View Assessment Answers Feature

## Date
October 29, 2025

## Feature Summary
Added a feature that allows students to review their assessment answers after submission. Students can view all their submitted answers, see which ones were correct/incorrect, and view the correct answers for questions they got wrong.

## Implementation

### Backend

#### 1. New Controller Method
**File**: `app/Http/Controllers/AssessmentController.php`

**Method**: `getAssessmentResponses()`
- Fetches all quiz attempts for the authenticated student
- Groups responses by category and subcategory
- Returns question text, code snippets, all answer options, selected answer, and correctness

**Response Structure**:
```json
{
    "student_name": "John Doe",
    "submitted_at": "2025-10-29T12:00:00",
    "categories": [
        {
            "category_name": "Technical Skill",
            "subcategories": [
                {
                    "subcategory_name": "General Programming Concepts",
                    "questions": [
                        {
                            "question_id": 1,
                            "question_text": "What should be the output of the code",
                            "code_snippet": "print(\"Hello World\")",
                            "question_type": "multiple_choice",
                            "points": 1,
                            "all_answers": [
                                {"id": 1, "text": "Hi World", "is_correct": false},
                                {"id": 2, "text": "Hello World", "is_correct": true}
                            ],
                            "selected_answer_id": 2,
                            "selected_answer_text": "Hello World",
                            "is_correct": true,
                            "points_earned": 1
                        }
                    ]
                }
            ]
        }
    ]
}
```

#### 2. New Route
**File**: `routes/web.php`

```php
Route::get('assessment/responses', [AssessmentController::class, 'getAssessmentResponses'])
    ->name('assessment.responses');
```

### Frontend

#### 1. New Component
**File**: `resources/js/components/ViewAssessmentAnswers.tsx`

**Features**:
- Dialog modal to view assessment responses
- Collapsible categories and subcategories
- Color-coded answers:
  - ✅ Green: Correct answer (selected or not)
  - ❌ Red: Incorrect answer (if selected)
  - White: Other options
- Code snippet display support
- Shows points earned vs total points
- Submission timestamp

**UI Structure**:
```
Dialog
├── Category (Collapsible)
│   ├── Subcategory (Collapsible)
│   │   ├── Question 1
│   │   │   ├── Question Text
│   │   │   ├── Code Snippet (if any)
│   │   │   ├── All Answer Options
│   │   │   └── Points Info
│   │   └── Question 2...
│   └── Subcategory 2...
└── Category 2...
```

#### 2. Integration
**File**: `resources/js/pages/student/profile.tsx`

- Added import for `ViewAssessmentAnswers` component
- Placed button in the scores section (Assessment Status card)
- Only visible when student has submitted assessment

## User Experience

### For Students

**Location**: Student Profile → Assessment Status section

**Button**: "View Assessment Answers" (appears below scores)

**Features**:
1. Click button to open modal
2. See all questions organized by category/subcategory
3. Review answers with visual indicators:
   - Your answer highlighted
   - Correct answer shown
   - Code snippets displayed
4. See points earned for each question
5. Navigate through collapsible sections

### Visual Indicators

```
Question: What should be the output of the code

┌─────────────────────────┐
│ print("Hello World")    │  ← Code snippet
└─────────────────────────┘

Your answer: Hello World ✅  ← Green (correct)
Correct answer: Hello World  ← Also shown if you got it wrong

Points: 1 / 1
```

## Benefits

1. **Learning Tool**: Students can review mistakes and learn from them
2. **Transparency**: Students see exactly how they were graded
3. **Self-Assessment**: Helps students identify weak areas
4. **Feedback**: Immediate visual feedback on performance
5. **Code Review**: Can review code snippets they answered

## Technical Details

### Database
- Uses existing `quiz_attempts` table
- Relationships: `student`, `question`, `selected_answer`

### API Endpoint
- **URL**: `/assessment/responses`
- **Method**: GET
- **Auth**: Required (student must be logged in)
- **Requirements**: Student must have submitted assessment

### Error Handling
- Returns 404 if student not found
- Returns 404 if assessment not submitted
- Returns 500 on server error
- Frontend shows loading state and error messages

### Performance
- Single API call loads all responses
- Data grouped on backend (reduces frontend processing)
- Collapsible sections improve performance for large datasets

## Files Created/Modified

### Backend
- ✅ `app/Http/Controllers/AssessmentController.php` - Added `getAssessmentResponses()` method
- ✅ `routes/web.php` - Added assessment responses route

### Frontend
- ✅ `resources/js/components/ViewAssessmentAnswers.tsx` - New component
- ✅ `resources/js/pages/student/profile.tsx` - Integrated component

### Technical Notes
- Used regular `div` with `overflow-y-auto` instead of ScrollArea component (which doesn't exist in the UI library)
- Implemented custom scrolling with Tailwind CSS classes

## Testing Checklist

- ✅ Frontend builds without errors
- ⏳ API endpoint returns correct data structure
- ⏳ Student can open assessment answers modal
- ⏳ Correct/incorrect answers are visually distinct
- ⏳ Code snippets display properly
- ⏳ Points earned displayed correctly
- ⏳ Collapsible sections work
- ⏳ Only visible after assessment submission

## Future Enhancements (Optional)

1. Add export to PDF functionality
2. Add filter/search within answers
3. Add statistics (e.g., "You got 80% in Technical Skills")
4. Allow printing of assessment results
5. Add comparison with class average
6. Show time spent on each question

