# Centralized Deadline Notification System

## Overview

The centralized deadline notification system replaces the previous role-specific notification services with a unified approach that handles all deadline notifications for all user roles (admin, student, HTE, adviser) in a single, maintainable system.

## Key Benefits

1. **Centralized Management**: Single service handles all deadline notifications
2. **Reduced Code Duplication**: Eliminates separate notification classes for each role
3. **Consistent User Experience**: Unified notification format across all roles
4. **Easy Maintenance**: Changes to notification logic only need to be made in one place
5. **Scalable**: Easy to add new roles or deadline categories
6. **Admin Notifications**: Admins now receive notifications for all deadline types

## Architecture

### Core Components

1. **UnifiedDeadlineNotification** (`app/Notifications/UnifiedDeadlineNotification.php`)
   - Single notification class for all roles
   - Uses blade template for consistent email formatting
   - Handles role-specific content and actions

2. **CentralizedDeadlineNotificationService** (`app/Services/CentralizedDeadlineNotificationService.php`)
   - Main service for processing deadline notifications
   - Handles role-to-category mapping
   - Manages notification timing and urgency levels

3. **Unified Blade Template** (`resources/views/emails/unified-deadline.blade.php`)
   - Responsive email template
   - Dynamic urgency level styling
   - Role-specific content sections

4. **Console Command** (`app/Console/Commands/ProcessDeadlineNotifications.php`)
   - Command-line interface for processing notifications
   - Supports filtering by user, role, or cleanup operations

## Role-to-Deadline Mapping

| Role | Deadline Categories |
|------|-------------------|
| **Admin** | All categories (student_verification, sip_endorsement, student_assessment_form, hte_assessment_form, student_placements_by_hte) |
| **Adviser** | student_verification |
| **Student** | student_assessment_form, student_placements_by_hte |
| **HTE** | hte_assessment_form, student_placements_by_hte |

## Notification Timing

### Day-based Reminders
- **5 days**: Medium priority reminder
- **3 days**: High priority reminder
- **1 day**: Urgent reminder

### Hour-based Reminders
- **< 24 hours**: Urgent reminder (shows hours remaining)
- **< 2 hours**: Critical reminder

## Usage

### Automatic Processing

The system automatically processes notifications when:
- New deadlines are created
- Deadlines are updated
- Deadlines are extended

### Manual Processing

```bash
# Process notifications for all users
php artisan deadlines:process-notifications

# Process notifications for a specific user
php artisan deadlines:process-notifications --user-id=123

# Process notifications for a specific role
php artisan deadlines:process-notifications --role=student

# Cleanup old notifications
php artisan deadlines:process-notifications --cleanup
```

### Testing Notifications

```bash
# Test all email notifications including unified deadline notifications
php artisan test:email-notifications your-email@example.com
```

## Configuration

### Adding New Roles

To add a new role to the notification system:

1. Update the `ROLE_DEADLINE_MAPPING` constant in `CentralizedDeadlineNotificationService.php`
2. Add role-specific content in `UnifiedDeadlineNotification.php`
3. Update the blade template if needed

### Adding New Deadline Categories

1. Add the category to the `ROLE_DEADLINE_MAPPING` constant
2. Update the `getDefaultDeadlineName()` method
3. Add role-specific content for the new category

## Database Schema

The system uses the existing `notifications` table with:
- `type`: 'unified_deadline'
- `data`: JSON containing deadline information and urgency levels

## Migration from Old System

The old role-specific services are still present but can be safely removed:
- `DeadlineNotificationService.php` (HTE-specific)
- `StudentDeadlineNotificationService.php` (Student-specific)
- `HTEDeadlineNotification.php` (HTE-specific notification)
- `StudentDeadlineNotification.php` (Student-specific notification)

## Admin Benefits

Admins now receive notifications for:
- **Student Verification**: When advisers need to verify students
- **SIP Endorsement**: When automatic endorsements need to be processed
- **Student Assessment**: Monitor student completion rates
- **HTE Assessment**: Monitor HTE participation
- **Student Placements**: Monitor placement completion

## Email Template Features

- **Responsive Design**: Works on desktop and mobile
- **Urgency Indicators**: Color-coded priority levels
- **Role-Specific Content**: Tailored information for each role
- **Action Buttons**: Direct links to relevant dashboard sections
- **Professional Styling**: Consistent with BulSU branding

## Monitoring and Logging

The system includes comprehensive logging:
- Notification creation and updates
- User-specific processing
- Error handling and debugging
- Performance metrics

## Future Enhancements

Potential improvements:
1. **SMS Notifications**: Add SMS support for critical deadlines
2. **Push Notifications**: Browser push notifications
3. **Customizable Timing**: Allow users to set their own reminder preferences
4. **Bulk Operations**: Process multiple deadlines simultaneously
5. **Analytics**: Track notification effectiveness and user engagement

## Troubleshooting

### Common Issues

1. **Notifications not sending**: Check email configuration and queue workers
2. **Wrong users receiving notifications**: Verify role assignments and deadline categories
3. **Template errors**: Check blade template syntax and variable availability

### Debug Commands

```bash
# Check notification queue
php artisan queue:work --verbose

# Test specific user notifications
php artisan deadlines:process-notifications --user-id=123

# View notification logs
tail -f storage/logs/laravel.log | grep "deadline notification"
```

## Security Considerations

- All notifications are queued to prevent blocking
- User data is properly sanitized in templates
- Role-based access control is enforced
- Sensitive information is not included in notifications

This centralized system provides a robust, maintainable solution for deadline notifications across all user roles while ensuring administrators stay informed about all critical deadlines in the system.
