<!-- 7b1f3759-e400-4dbd-a4b9-3d4a223ae5ed 72f683db-ddef-49bd-b412-4e922380a915 -->
# Internship Season Management System

## Overview

Create a parent "Internship Season" entity that contains all deadline categories, with automatic student archiving capabilities and username reuse for archived accounts.

## Database Changes

### 1. Create Internship Seasons Table

**Migration**: `create_internship_seasons_table.php`

- `id` (primary key)
- `name` (string) - e.g., "AY 2024-2025 First Semester"
- `start_date` (datetime)
- `end_date` (datetime)
- `status` (enum: 'active', 'completed', 'archived')
- `timestamps`

**Constraints**:

- Only one season can have `status='active'` at a time
- Index on `status` for performance

### 2. Update Deadlines Table

**Migration**: `add_internship_season_id_to_deadlines_table.php`

- Add `internship_season_id` (foreign key to `internship_seasons.id`)
- Add new deadline category: `'archive_students'` to the enum
- Update validation to prevent duplicate categories within same season

### 3. Update Students Table

**Migration**: `add_season_id_to_students_table.php`

- Add `internship_season_id` (foreign key, nullable) - tracks which season student belongs to
- Add unique constraint: `unique(['student_number', 'is_active'])` to allow reuse of archived student numbers

## Models

### 4. Create InternshipSeason Model

**File**: `app/Models/InternshipSeason.php`

- Relationships: `hasMany(Deadline::class)`, `hasMany(Student::class)`
- Scopes: `scopeActive()`, `scopeCompleted()`, `scopeArchived()`
- Methods:
  - `isActive()`: Check if season is currently active
  - `hasActiveDeadlines()`: Check if any deadlines are still active
  - `canArchive()`: Validate if season can be archived (all deadlines expired)
  - `archiveStudents()`: Archive all students in this season
  - Static `getActiveSeason()`: Get the current active season
  - Static `ensureOnlyOneActive()`: Validation to prevent multiple active seasons

### 5. Update Deadline Model

**File**: `app/Models/Deadline.php`

- Add `belongsTo(InternshipSeason::class)` relationship
- Update `getCategoryDisplayName()` to include "Archive Students"
- Update `getCategorySequence()` to add `'archive_students' => 5`
- Update validation methods to check within season context
- Add method `isArchiveStudentsDeadline()` to check if deadline is for archiving

### 6. Update Student Model

**File**: `app/Models/Student.php`

- Add `belongsTo(InternshipSeason::class)` relationship
- Update scopes to filter by active status
- Add method `canBeReused()`: Check if archived student number can be reused

## Services

### 7. Create InternshipSeasonService

**File**: `app/Services/InternshipSeasonService.php`

- `createSeason($name, $startDate, $endDate)`: Create new season
- `activateSeason($seasonId)`: Set season as active, deactivate others
- `completeSeason($seasonId)`: Mark season as completed
- `archiveSeasonStudents($seasonId)`: Archive all students in a season
- `validateSeasonTransition()`: Ensure only one active season

### 8. Update StudentArchiveService

**File**: `app/Services/StudentArchiveService.php` (new or enhance existing)

- `archiveStudentsBySeason($seasonId)`: Bulk archive students
- `canReuseStudentNumber($studentNumber)`: Check if student number is available (not active)
- `validateStudentNumberReuse($studentNumber)`: Validate reuse logic

### 9. Update DeadlineStatusService

**File**: `app/Services/DeadlineStatusService.php`

- Add logic to detect when "Archive Students" deadline expires
- Trigger automatic student archiving when archive deadline ends
- Update season status to 'completed' after archiving

## Controllers

### 10. Create InternshipSeasonController

**File**: `app/Http/Controllers/InternshipSeasonController.php`

- `index()`: Display all seasons with deadline counts
- `store()`: Create new season
- `update()`: Update season details
- `activate()`: Set season as active
- `complete()`: Mark season as completed
- `archiveStudents()`: Trigger student archiving for season

### 11. Update AdminController

**File**: `app/Http/Controllers/AdminController.php`

- Update `events()` method to pass current season info
- Update `storeDeadline()` to associate with active season
- Update validation to check season context for deadline categories
- Add route for managing seasons

### 12. Update Auth/RegisteredUserController

**File**: `app/Http/Controllers/Auth/RegisteredUserController.php`

- Update username validation from `unique:users,username` to check only active students
- Modify validation: `Rule::unique('users', 'username')->where(fn($q) => $q->whereHas('student', fn($sq) => $sq->where('is_active', true)))`
- Or use custom validation rule: `'username' => ['required', new UniqueActiveStudentNumber]`

## Validation Rules

### 13. Create Custom Validation Rule

**File**: `app/Rules/UniqueActiveStudentNumber.php`

- Validate that student_number is unique among active students only
- Allow reuse if existing student is inactive (`is_active = false`)

## Frontend Components

### 14. Create Season Management Page

**File**: `resources/js/pages/admin/seasons.tsx`

- List all seasons with status indicators
- Create new season form
- View season deadlines
- Archive students button (enabled when archive deadline expires)
- Season activation controls

### 15. Update Events Page

**File**: `resources/js/pages/admin/events.tsx`

- Display current active season name
- Show season context for all deadlines
- Add "Archive Students" to category options
- Update deadline creation to associate with active season

### 16. Create Season Selector Component

**File**: `resources/js/components/SeasonSelector.tsx`

- Dropdown to view different seasons
- Display season status
- Quick navigation between season management

## Routes

### 17. Add Season Routes

**File**: `routes/web.php`

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/seasons', [InternshipSeasonController::class, 'index'])->name('admin.seasons');
    Route::post('/admin/seasons', [InternshipSeasonController::class, 'store'])->name('admin.seasons.store');
    Route::put('/admin/seasons/{season}', [InternshipSeasonController::class, 'update'])->name('admin.seasons.update');
    Route::post('/admin/seasons/{season}/activate', [InternshipSeasonController::class, 'activate'])->name('admin.seasons.activate');
    Route::post('/admin/seasons/{season}/archive-students', [InternshipSeasonController::class, 'archiveStudents'])->name('admin.seasons.archive-students');
});
```

## Automated Processes

### 18. Update Deadline Status Command

**File**: `app/Console/Commands/UpdateDeadlineStatuses.php`

- Add logic to detect expired "Archive Students" deadlines
- Automatically trigger student archiving when archive deadline expires
- Update season status to 'completed'

### 19. Create Season Cleanup Job

**File**: `app/Jobs/ArchiveSeasonStudentsJob.php`

- Queue job for bulk student archiving
- Preserve student records, placements, scores, matches
- Update `is_active = false` and set `internship_season_id`

## Business Logic

### Key Rules Implementation:

1. **One Active Season**: Enforce via database constraint + validation
2. **Season-Deadline Association**: All deadlines must belong to a season
3. **Deadline Category Uniqueness**: Within a season, only one active deadline per category
4. **Archive Students Deadline**: New 5th category in sequence, triggers automatic archiving
5. **Student Number Reuse**: Inactive students don't block username registration
6. **Data Preservation**: Archiving sets `is_active=false` but preserves all related data
7. **Season Completion**: Automatic when archive students deadline expires

## Migration Order

1. `create_internship_seasons_table`
2. `add_internship_season_id_to_deadlines_table`
3. `add_season_id_to_students_table`
4. Seed initial season for existing deadlines (data migration)

### To-dos

- [ ] Create migration for internship_seasons table with unique active season constraint
- [ ] Add internship_season_id to deadlines table and archive_students category
- [ ] Add internship_season_id to students table with unique constraint on student_number + is_active
- [ ] Create InternshipSeason model with relationships and validation methods
- [ ] Update Deadline model for season relationship and archive_students category
- [ ] Add season relationship to Student model
- [ ] Create InternshipSeasonService for season management logic
- [ ] Create/update StudentArchiveService for bulk archiving and reuse validation
- [ ] Create UniqueActiveStudentNumber validation rule
- [ ] Create InternshipSeasonController with CRUD and archiving actions
- [ ] Update AdminController to handle season context in deadline management
- [ ] Update registration validation to allow archived student number reuse
- [ ] Update UpdateDeadlineStatuses command for automatic archiving trigger
- [ ] Create ArchiveSeasonStudentsJob for background student archiving
- [ ] Create admin seasons management page (React/TypeScript)
- [ ] Update events page to show season context and archive_students category
- [ ] Add season management routes to web.php
- [ ] Create seeder to associate existing deadlines with a default season