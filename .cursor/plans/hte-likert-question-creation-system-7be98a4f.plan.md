<!-- 7be98a4f-a851-4344-a447-88714717c5bd eef5d07c-e860-47fa-a64c-32ea0deb1bae -->
# HTE Likert Question Creation System

## Overview

Modify the question creation system to allow admins to create paired questions: student quiz questions with corresponding HTE Likert questions. The HTE form will use these HTE-specific Likert questions instead of default student questions.

## Database Changes

### 1. Create Migration: Add `hte_question_id` foreign key to questions table

- File: `database/migrations/YYYY_MM_DD_HHMMSS_add_hte_question_id_to_questions_table.php`
- Add nullable `hte_question_id` foreign key column that references `questions.id`
- This links student quiz questions to their corresponding HTE Likert questions
- Add index on `hte_question_id` for performance

## Backend Changes

### 2. Update Question Model

- File: `app/Models/Question.php`
- Add `hte_question_id` to fillable array
- Add relationship method: `hteQuestion()` - belongsTo relationship to HTE question
- Add relationship method: `studentQuestion()` - hasOne relationship to student question (reverse)

### 3. Update QuestionController

- File: `app/Http/Controllers/QuestionController.php`
- Modify `store()` method:
- Add validation for `hte_question` (required string)
- Create student question (quiz type) first
- Create HTE question (rating type) second with `question_type = 'rating'`
- Link them via `hte_question_id` foreign key
- Modify `update()` method:
- Add validation for `hte_question` (required string)
- Update student question
- Update or create HTE question (check if linked HTE question exists)
- Maintain the link between questions
- Modify `index()` method:
- Include HTE question data when loading questions (via relationship)
- Return both student and HTE question data for display

### 4. Update HTEController

- File: `app/Http/Controllers/HTEController.php`
- Modify `getCategoriesForCriteria()` method:
- Filter questions to only return HTE questions (`question_type = 'rating'` and `hte_question_id IS NOT NULL`)
- Or alternatively: Load questions with their `hte_question_id` relationship and filter appropriately
- Ensure only HTE questions are returned for rating

## Frontend Changes

### 5. Update Forms Page (forms.tsx)

- File: `resources/js/pages/admin/forms.tsx`
- Modify form structure:
- Add new section in the form card: "HTE Question" section below "Choices" section
- Add Textarea input for HTE question text with label "HTE Question (Likert Scale)"
- Add helper text indicating this is always Likert scale (1-5)
- Update form state to include `hteQuestion` field
- Update validation to require HTE question text
- Update `handleSubmit()` to send both `question` and `hteQuestion` to backend
- Update `handleEdit()` to load and populate HTE question when editing
- Display HTE question text when editing existing questions

### 6. Update Question Type Interface

- File: `resources/js/types/index.d.ts`
- Update `Question` interface:
- Add optional `hte_question_id?: number`
- Add optional `hte_question?: Question` for nested HTE question data
- Ensure `question_type` can be 'quiz' or 'rating'

### 7. Update HTE Form (if needed)

- File: `resources/js/components/form/hte/criteria.tsx`
- Verify that HTE questions are properly displayed (should already work if backend filtering is correct)
- Ensure only HTE questions (rating type) are shown for Likert rating

## Implementation Details

### Question Creation Flow

1. Admin fills in student question section (question text + choices)
2. Admin fills in HTE question section (question text only)
3. On submit:

- Create student question with `question_type = 'quiz'`
- Create HTE question with `question_type = 'rating'`, same `subcategory_id`
- Link student question to HTE question via `hte_question_id`

### Question Editing Flow

1. Load both student and HTE question data
2. Display both sections in the form
3. Update both questions on save

### HTE Form Display

- HTE form will fetch questions filtered by `question_type = 'rating'` and `hte_question_id IS NOT NULL`
- These are the questions HTE users rate on Likert scale

## Testing Considerations

- Verify student questions still work in student assessment form
- Verify HTE questions appear correctly in HTE criteria step
- Verify editing updates both questions correctly
- Verify archiving works (should archive both linked questions)
- Verify question deletion cascade works properly

### To-dos

- [ ] Create migration to add hte_question_id foreign key to questions table
- [ ] Update Question model to add hte_question_id to fillable and add relationship methods
- [ ] Update QuestionController store() method to create both student and HTE questions
- [ ] Update QuestionController update() method to handle updating both questions
- [ ] Update QuestionController index() to include HTE question data
- [ ] Update HTEController getCategoriesForCriteria() to filter HTE questions correctly
- [ ] Add HTE Question section to forms.tsx form card with textarea input
- [ ] Update forms.tsx state and validation to include HTE question field
- [ ] Update handleSubmit() and handleEdit() in forms.tsx to handle HTE question data
- [ ] Update TypeScript interfaces to include hte_question_id and hte_question data