# HTE Questions Separation Implementation

## Overview
This document describes the separation of HTE assessment questions from student quiz questions into dedicated tables.

## Problem
Previously, the `HTEAssessmentSeeder` was storing HTE criteria questions into the general `questions` table, which is intended for student quiz questions with multiple choice answers, points, and question types. This caused confusion and mixed different assessment types.

## Solution
Created a separate `hte_questions` table specifically for HTE assessment criteria questions that use Likert scale (1-5) ratings.

## Changes Made

### 1. Database Structure

#### New Table: `hte_questions`
```sql
- id (primary key)
- question (text)
- subcategory_id (foreign key to sub_categories)
- is_active (boolean, default: true)
- created_at
- updated_at
```

#### Updated Table: `questions`
Added missing columns that were referenced in the Question model:
```sql
- question_type (string, nullable)
- points (decimal, nullable)
```

### 2. Models

#### New Model: `HTEQuestion`
- Location: `app/Models/HTEQuestion.php`
- Purpose: Manage HTE assessment criteria questions
- Relationships: belongsTo SubCategory
- Scope: active() - filters active questions

#### Updated Model: `Answer`
- Added fillable fields: `question_id`, `answer_text`, `is_correct`, `display_order`
- Added casts for proper type handling
- Added relationship to Question model
- Note: This is used for student quiz questions with multiple choice answers

### 3. Seeders

#### Updated: `HTEAssessmentSeeder`
- Changed from using `Question` model to `HTEQuestion` model
- Removed `question_type` and `points` fields (not needed for Likert scale)
- Seeds 25 subcategories with ~85 questions total
- Categories: Technical Skill, Soft Skill

### 4. Controllers

#### New Controller: `HTECriteriaController`
- Location: `app/Http/Controllers/HTECriteriaController.php`
- Methods:
  - `index()` - Display paginated list with filters
  - `store()` - Create new HTE question
  - `update()` - Update existing HTE question
  - `archive()` - Soft delete (set is_active = false)
  - `restore()` - Restore archived question
  - `getSubcategories()` - Get subcategories for a category

### 5. Routes

Added to `routes/web.php`:
```php
Route::get('forms/hte-criteria', [HTECriteriaController::class, 'index'])->name('admin.hte-criteria');
Route::post('forms/hte-criteria', [HTECriteriaController::class, 'store'])->name('admin.hte-criteria.store');
Route::put('forms/hte-criteria/{hteQuestion}', [HTECriteriaController::class, 'update'])->name('admin.hte-criteria.update');
Route::patch('forms/hte-criteria/{hteQuestion}/archive', [HTECriteriaController::class, 'archive'])->name('admin.hte-criteria.archive');
Route::patch('forms/hte-criteria/{hteQuestion}/restore', [HTECriteriaController::class, 'restore'])->name('admin.hte-criteria.restore');
Route::get('forms/hte-criteria/categories/{category}/subcategories', [HTECriteriaController::class, 'getSubcategories'])->name('admin.hte-criteria.categories.subcategories');
```

### 6. Frontend

#### New Page: `hte-criteria.tsx`
- Location: `resources/js/pages/admin/hte-criteria.tsx`
- Features:
  - Paginated table view of HTE questions
  - Add/Edit form with category and subcategory selection
  - Search and filter functionality
  - Archive/Restore actions
  - Responsive design with Tailwind CSS

#### Updated Sidebar: `app-sidebar.tsx`
Added "HTE Criteria" menu item under "Form" section in admin navigation:
```typescript
{
    title: 'Form',
    href: '/form',
    icon: NotepadTextIcon,
    subNav: [
        { title: 'Additional Info Tab', href: '/forms/additional-info' },
        { title: 'Student Assessment', href: '/forms/assessment' },
        { title: 'HTE Criteria', href: '/forms/hte-criteria' }, // NEW
    ],
}
```

## Table Purposes

### `questions` Table
- **Purpose**: Student quiz/assessment questions
- **Question Types**: multiple_choice, true_false, identification, essay, enumeration
- **Scoring**: Points-based system
- **Answers**: Stored in `answers` table with `is_correct` flag
- **Used By**: Students taking assessments
- **Controller**: `QuestionController`
- **Route**: `/forms/assessment`

### `hte_questions` Table
- **Purpose**: HTE company assessment criteria
- **Question Type**: Likert scale (1-5) rating
- **Scoring**: Weight-based calculation
- **Answers**: Direct rating values (no separate answers table)
- **Used By**: HTE companies evaluating internship criteria
- **Controller**: `HTECriteriaController`
- **Route**: `/forms/hte-criteria`

## Migration Instructions

To apply these changes to an existing database:

```bash
# Run migrations
php artisan migrate

# Seed HTE questions
php artisan db:seed --class=HTEAssessmentSeeder
```

## Future Considerations

1. **Data Migration**: If there are existing HTE questions in the `questions` table, create a migration to move them to `hte_questions`

2. **Student Quiz Questions**: The `questions` table is now ready for admin to add student assessment questions with proper answers, question types, and points

3. **HTE Assessment Responses**: Ensure `hte_assessment_responses` table references `hte_questions` instead of `questions` for proper foreign key relationships

4. **Weight Allocation**: HTE questions should have weight values stored in `subcategory_weights` table for proper scoring calculations

## Testing Checklist

- [x] Migrations run successfully
- [x] HTE questions seeded correctly
- [x] Admin can access HTE Criteria page
- [x] Admin can add new HTE questions
- [x] Admin can edit HTE questions
- [x] Admin can archive/restore HTE questions
- [x] Filters work correctly
- [x] Pagination works
- [ ] HTE assessment form uses hte_questions
- [ ] Student assessment form uses questions table
- [ ] Weight calculations use correct table

## Related Files

- Migrations:
  - `2025_10_27_000000_add_question_type_and_points_to_questions_table.php`
  - `2025_10_27_000001_create_hte_questions_table.php`
  
- Models:
  - `app/Models/Question.php`
  - `app/Models/HTEQuestion.php`
  - `app/Models/Answer.php`
  
- Controllers:
  - `app/Http/Controllers/QuestionController.php`
  - `app/Http/Controllers/HTECriteriaController.php`
  
- Seeders:
  - `database/seeders/HTEAssessmentSeeder.php`
  
- Frontend:
  - `resources/js/pages/admin/forms.tsx` (Student Assessment)
  - `resources/js/pages/admin/hte-criteria.tsx` (HTE Criteria)
  - `resources/js/components/app-sidebar.tsx`

