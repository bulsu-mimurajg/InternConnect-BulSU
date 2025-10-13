# Automatic Internship Season Status Management

## Overview

The system now automatically manages internship season statuses based on start/end dates and deadline requirements. This ensures seasons are activated and deactivated at the appropriate times without manual intervention.

## How It Works

### Automatic Status Logic

The system determines the correct status for each season based on:

1. **Current Date vs Season Dates**
   - Before start date: `inactive`
   - After end date: `completed`
   - Within date range: depends on deadline requirements

2. **Deadline Requirements**
   - All 5 required deadline categories must exist:
     - HTE Assessment Form
     - Student Verification
     - Student Assessment Form
     - Internship Placement
     - Archive Students

3. **Status Transitions**
   - `inactive` → `active`: When current date is within season range AND all deadlines exist
   - `active` → `completed`: When current date is past the end date

### Automatic Activation

When a season should be active:
- System checks if another season is currently active
- If another season is active, it automatically deactivates it first
- Validates all required deadline categories exist
- Updates status to `active`
- Logs the activation

### Automatic Deactivation

When a season should be completed:
- Expires all active deadlines in the season
- Marks season as `completed`
- Triggers automatic placement if internship placement deadline was active
- Logs the deactivation


## Scheduled Command

### UpdateSeasonStatusesCommand

A Laravel command that can be run manually or scheduled:

```bash
# Manual execution
php artisan seasons:update-statuses

# Dry run (shows what would change without making changes)
php artisan seasons:update-statuses --dry-run
```

### Scheduling

Add to `app/Console/Kernel.php` to run automatically:

```php
protected function schedule(Schedule $schedule)
{
    // Check season statuses every hour
    $schedule->command('seasons:update-statuses')->hourly();
}
```

## Frontend Indicators

### Status Badges

- **🟢 Active**: Season is currently active
- **⚪ Inactive**: Season is not active
- **✅ Completed**: Season has ended
- **📁 Archived**: Season is archived

## API Endpoints

### New Routes

- `POST /admin/seasons/{season}/activate` - Activate a season (with validation)
- `POST /admin/seasons/{season}/deactivate` - Deactivate a season

## Error Handling

### Common Issues

1. **Missing Deadlines**
   - Season won't auto-activate without all 5 deadline categories
   - Use "Force Activate" if needed (with caution)

2. **Multiple Active Seasons**
   - System automatically deactivates current active season before activating new one
   - Ensures only one season is active at a time

3. **Date Conflicts**
   - Seasons with overlapping date ranges are handled by deactivating the current active season

### Logging

All automatic status changes are logged with:
- Season ID and name
- Previous and new status
- Reason for change
- Timestamp

## Best Practices

1. **Set Appropriate Dates**
   - Start date: When season should begin
   - End date: When season should end
   - Ensure dates don't overlap unnecessarily

2. **Create All Deadlines**
   - Create all 5 required deadline categories before season starts
   - This ensures automatic activation works properly

3. **Monitor Status Changes**
   - Check logs for automatic status changes
   - Verify status transitions work correctly


## Testing

Run the test suite to verify functionality:

```bash
php artisan test tests/Feature/Commands/UpdateSeasonStatusesCommandTest.php
```

The tests cover:
- Automatic activation of seasons within date range
- Automatic deactivation of seasons past end date
- Dry-run mode functionality
- Handling of seasons without required deadlines
