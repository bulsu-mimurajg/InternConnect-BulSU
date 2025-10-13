# Deadline Inactive Status Implementation

## Overview

The system now includes an "inactive" status for deadlines that automatically manages deadline statuses based on start and end dates. This ensures deadlines are properly categorized as inactive, active, or expired based on the current date.

## How It Works

### Automatic Status Logic

The system determines the correct status for each deadline based on:

1. **Current Date vs Deadline Dates**
   - Before start date: `inactive`
   - Within date range: `active`
   - After end date: `expired`

2. **Status Transitions**
   - `inactive` → `active`: When current date reaches start date
   - `active` → `expired`: When current date passes end date
   - `active` → `inactive`: When current date is before start date (edge case)

### Database Changes

#### Migration: `add_inactive_status_to_deadlines.php`

```php
// Updates the status enum to include 'inactive'
DB::statement("ALTER TABLE deadlines MODIFY COLUMN status ENUM('inactive', 'active', 'expired') DEFAULT 'inactive'");

// Updates existing deadlines to have proper status based on dates
DB::statement("
    UPDATE deadlines 
    SET status = CASE 
        WHEN end_date <= NOW() THEN 'expired'
        WHEN start_date > NOW() THEN 'inactive'
        ELSE 'active'
    END
");
```

## Model Enhancements

### Deadline Model Methods

#### New Methods Added:

1. **`isInactive()`**: Check if deadline is inactive
2. **`getAutomaticStatus()`**: Get the correct status based on current date
3. **`isStatusCorrect()`**: Check if current status matches expected status
4. **`getStatusTransitionReason()`**: Get reason for status transition

#### Updated Methods:

1. **`boot()` method**: Automatically sets status on save based on dates
2. **`isActiveForCategory()`**: Now includes start date check
3. **`getActiveForCategory()`**: Now includes start date check
4. **`getActive()`**: Now includes start date check

#### New Static Methods:

1. **`isInactiveForCategory()`**: Check if category has inactive deadline
2. **`getInactiveForCategory()`**: Get inactive deadline for category
3. **`getInactive()`**: Get all inactive deadlines
4. **`checkAndUpdateStatuses()`**: Bulk update deadline statuses
5. **`getDeadlinesNeedingAttention()`**: Get deadlines with status mismatches

## Command Line Interface

### UpdateDeadlineStatusesCommand

A Laravel command to update deadline statuses:

```bash
# Manual execution
php artisan deadlines:update-statuses

# Dry run (shows what would change without making changes)
php artisan deadlines:update-statuses --dry-run
```

#### Command Features:
- Updates all deadline statuses based on current date
- Shows detailed results of changes made
- Supports dry-run mode for testing
- Comprehensive error handling and logging

## Frontend Integration

### Seasons Management Page Updates

#### New Interface Types:
```typescript
interface DeadlineNeedingAttention {
  deadline: {
    id: number;
    title: string;
    category: string;
    start_date: string;
    end_date: string;
    status: string;
  };
  current_status: string;
  should_be_status: string;
  reason: string;
}
```

#### New UI Components:

1. **Deadlines Needing Attention Panel**
   - Shows deadlines with status mismatches
   - Displays current vs expected status
   - Provides auto-fix button
   - Shows deadline date ranges

2. **Enhanced Update Statuses Button**
   - Shows combined count of seasons and deadlines needing attention
   - Processes both seasons and deadlines in one action

## Backend Integration

### InternshipSeasonService Updates

The `checkAndUpdateSeasonStatuses()` method now:
1. First updates all deadline statuses
2. Then updates season statuses
3. Includes deadline results in the response
4. Provides comprehensive logging

### Controller Updates

The `InternshipSeasonController::index()` method now includes:
- `deadlinesNeedingAttention` data
- Passes deadline information to frontend

## Status Logic Examples

### Scenario 1: Deadline Before Start Date
```php
$deadline = Deadline::create([
    'start_date' => Carbon::now()->addDays(5),
    'end_date' => Carbon::now()->addDays(10),
    'status' => 'inactive', // Automatically set
]);

// Result: status = 'inactive'
// Reason: "Deadline has not started yet"
```

### Scenario 2: Deadline Within Active Range
```php
$deadline = Deadline::create([
    'start_date' => Carbon::now()->subDays(2),
    'end_date' => Carbon::now()->addDays(3),
    'status' => 'active', // Automatically set
]);

// Result: status = 'active'
// Reason: "Deadline is currently active"
```

### Scenario 3: Deadline Past End Date
```php
$deadline = Deadline::create([
    'start_date' => Carbon::now()->subDays(10),
    'end_date' => Carbon::now()->subDays(5),
    'status' => 'expired', // Automatically set
]);

// Result: status = 'expired'
// Reason: "Deadline has expired"
```

## API Methods

### Static Methods for Category Checking

```php
// Check if category has inactive deadline
Deadline::isInactiveForCategory('hte_assessment_form');

// Get inactive deadline for category
Deadline::getInactiveForCategory('student_verification');

// Get all inactive deadlines
Deadline::getInactive();

// Check and update all deadline statuses
Deadline::checkAndUpdateStatuses();

// Get deadlines needing attention
Deadline::getDeadlinesNeedingAttention();
```

### Instance Methods

```php
$deadline = Deadline::find(1);

// Check status
$deadline->isInactive();
$deadline->isActive();
$deadline->isExpired();

// Get automatic status
$deadline->getAutomaticStatus();

// Check if status is correct
$deadline->isStatusCorrect();

// Get transition reason
$deadline->getStatusTransitionReason();
```

## Scheduling

### Automatic Updates

Add to `app/Console/Kernel.php` to run automatically:

```php
protected function schedule(Schedule $schedule)
{
    // Check deadline statuses every hour
    $schedule->command('deadlines:update-statuses')->hourly();
    
    // Check both seasons and deadlines every hour
    $schedule->command('seasons:update-statuses')->hourly();
}
```

## Testing

### Test Coverage

The implementation includes comprehensive tests covering:
- Automatic status updates
- Dry-run mode functionality
- Model method validation
- Status transition reasons
- Edge cases and error handling

### Running Tests

```bash
# Run deadline status tests
php artisan test tests/Feature/Commands/UpdateDeadlineStatusesCommandTest.php

# Run all related tests
php artisan test --filter=Deadline
```

## Error Handling

### Common Issues

1. **Date Format Issues**
   - Ensure start_date and end_date are proper Carbon instances
   - Use consistent timezone handling

2. **Status Mismatches**
   - Use `checkAndUpdateStatuses()` to fix mismatches
   - Monitor "Deadlines Needing Attention" panel

3. **Season Context**
   - All deadline operations now consider active season
   - No active season means no deadlines can be active/inactive

### Logging

All automatic status changes are logged with:
- Deadline ID and title
- Previous and new status
- Reason for change
- Timestamp

## Best Practices

1. **Date Management**
   - Set realistic start and end dates
   - Avoid overlapping deadlines unnecessarily
   - Use consistent timezone settings

2. **Status Monitoring**
   - Monitor logs for automatic status changes
   - Monitor logs for status change issues

3. **Testing**
   - Test deadline status changes in development
   - Use dry-run mode before production updates
   - Verify status transitions work correctly

## Migration Guide

### For Existing Systems

1. **Run the migration**:
   ```bash
   php artisan migrate
   ```

2. **Verify status updates**:
   ```bash
   php artisan deadlines:update-statuses --dry-run
   ```

3. **Apply status updates**:
   ```bash
   php artisan deadlines:update-statuses
   ```

4. **Schedule automatic updates**:
   Add to `app/Console/Kernel.php` as shown above

### For New Installations

The inactive status is now the default for new deadlines, and the system will automatically manage statuses based on dates.
