# Hostinger Queue Setup Guide

## Overview

This guide explains how to set up Laravel queues on Hostinger to prevent admin interface blocking when sending deadline notifications.

## Why Use Queues?

- **Non-blocking**: Admin interface responds immediately
- **Reliability**: Failed jobs can be retried
- **Scalability**: Process multiple emails in background
- **Performance**: Better user experience

## Setup Steps

### 1. Environment Configuration

Add these to your `.env` file:

```env
# Queue Configuration
QUEUE_CONNECTION=database
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
DB_QUEUE_RETRY_AFTER=90

# Failed Jobs
QUEUE_FAILED_DRIVER=database-uuids
```

### 2. Database Setup

Run the migrations to create the necessary tables:

```bash
php artisan migrate
```

This creates:
- `jobs` table - stores queued jobs
- `failed_jobs` table - stores failed jobs
- `job_batches` table - for job batching

### 3. Hostinger Cron Job Setup

#### Option A: Single Cron Job (Recommended)

1. Go to your Hostinger control panel
2. Navigate to **Cron Jobs**
3. Create a new cron job with these settings:

```bash
# Run every minute - only processes when jobs exist
* * * * * cd /path/to/your/project && php artisan hostinger:process-queue --queue=deadline-notifications --max-jobs=15 --timeout=45 >/dev/null 2>&1
```

**Alternative using the standard command:**
```bash
# Run every minute - checks if queue is empty first
* * * * * cd /path/to/your/project && php artisan queue:process --queue=deadline-notifications --max-jobs=20 --timeout=60 --check-empty >/dev/null 2>&1
```

#### Option B: Multiple Cron Jobs (For Heavy Load)

Create separate cron jobs for different purposes:

```bash
# Process deadline notifications every minute (optimized)
* * * * * cd /path/to/your/project && php artisan hostinger:process-queue --queue=deadline-notifications --max-jobs=10 --timeout=30 >/dev/null 2>&1

# Process general queue every 2 minutes
*/2 * * * * cd /path/to/your/project && php artisan queue:process --queue=default --max-jobs=15 --timeout=45 --check-empty >/dev/null 2>&1

# Clean up failed jobs daily
0 2 * * * cd /path/to/your/project && php artisan queue:prune-failed >/dev/null 2>&1
```

#### Option C: Conservative Approach (For Limited Resources)

If you have very limited resources, use longer intervals:

```bash
# Process every 2 minutes
*/2 * * * * cd /path/to/your/project && php artisan hostinger:process-queue --queue=deadline-notifications --max-jobs=5 --timeout=30 >/dev/null 2>&1

# Clean up failed jobs daily
0 3 * * * cd /path/to/your/project && php artisan queue:prune-failed >/dev/null 2>&1
```

### 4. Manual Queue Processing

For testing or manual processing:

```bash
# Process all deadline notifications
php artisan queue:process --queue=deadline-notifications

# Process with specific limits
php artisan queue:process --queue=deadline-notifications --max-jobs=10 --timeout=60

# Process all queues
php artisan queue:work --stop-when-empty
```

### 5. Monitoring Queue Status

```bash
# Check queue status
php artisan queue:monitor

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

## Queue Configuration Details

### Job Timeouts
- **ProcessDeadlineNotificationJob**: 5 minutes (300s)
- **ProcessAllDeadlineNotificationsJob**: 10 minutes (600s)

### Retry Logic
- **Individual Jobs**: 3 retries with 30s, 1m, 2m delays
- **Batch Jobs**: 2 retries with 1m, 3m delays

### Queue Names
- `deadline-notifications` - All deadline-related jobs
- `default` - General application jobs

## Testing the Setup

### 1. Test Queue Processing

```bash
# Test the queue command
php artisan queue:process --queue=deadline-notifications --max-jobs=5

# Test with verbose output
php artisan queue:work --queue=deadline-notifications --verbose --stop-when-empty
```

### 2. Test Email Notifications

```bash
# Test deadline notifications
php artisan test:email-notifications your-email@example.com

# Process deadline notifications manually
php artisan deadlines:process-notifications
```

### 3. Monitor Logs

Check your application logs for queue processing:

```bash
tail -f storage/logs/laravel.log | grep -i "queue\|deadline\|notification"
```

## Troubleshooting

### Common Issues

1. **Jobs Not Processing**
   - Check if cron job is running
   - Verify database connection
   - Check job table for stuck jobs

2. **Memory Issues**
   - Reduce `--max-jobs` parameter
   - Increase `--timeout` if needed
   - Check server memory limits

3. **Email Failures**
   - Verify EmailService configuration
   - Check Gmail app password
   - Review failed jobs table

### Debug Commands

```bash
# Check queue status
php artisan queue:monitor deadline-notifications

# View job details
php artisan tinker
>>> DB::table('jobs')->get();

# Clear all jobs
php artisan queue:clear

# Restart queue workers
php artisan queue:restart
```

## Performance Optimization

### Hostinger Resource Management

1. **Efficient Cron Jobs**
   ```bash
   # Only runs when jobs exist - prevents unnecessary resource usage
   * * * * * cd /path/to/your/project && php artisan hostinger:process-queue --queue=deadline-notifications --max-jobs=15 --timeout=45 >/dev/null 2>&1
   ```

2. **Resource Limits**
   - **Max Jobs**: 15 per minute (prevents memory issues)
   - **Timeout**: 45 seconds (prevents hanging processes)
   - **Memory**: Optimized for shared hosting
   - **CPU**: Minimal usage when queue is empty

3. **Job Batching for Efficiency**
   ```php
   // In your service - batch similar jobs together
   Bus::batch([
       new ProcessDeadlineNotificationJob($deadlineId, $userId1, $role),
       new ProcessDeadlineNotificationJob($deadlineId, $userId2, $role),
       // ... more jobs
   ])->onQueue('deadline-notifications')->dispatch();
   ```

4. **Database Optimization**
   - Add indexes to jobs table
   - Regular cleanup of old jobs
   - Monitor database performance
   - Use `--check-empty` to avoid unnecessary processing

## Security Considerations

1. **Cron Job Permissions**
   - Ensure proper file permissions
   - Use absolute paths
   - Limit cron job access

2. **Queue Security**
   - Validate job data
   - Implement rate limiting
   - Monitor for abuse

3. **Email Security**
   - Use app passwords, not main passwords
   - Implement email validation
   - Monitor for spam complaints

## Monitoring and Alerts

### Set up monitoring for:

1. **Queue Length**
   ```bash
   # Check if queue is backing up
   php artisan queue:monitor deadline-notifications --max=100
   ```

2. **Failed Jobs**
   ```bash
   # Alert if too many jobs fail
   php artisan queue:failed | wc -l
   ```

3. **Processing Time**
   - Monitor job execution time
   - Set up alerts for timeouts
   - Track performance metrics

## Backup and Recovery

1. **Database Backups**
   - Include jobs and failed_jobs tables
   - Regular backup schedule
   - Test recovery procedures

2. **Job Recovery**
   ```bash
   # Retry all failed jobs
   php artisan queue:retry all
   
   # Clear old failed jobs
   php artisan queue:prune-failed --hours=24
   ```

This setup ensures your deadline notifications are processed efficiently without blocking the admin interface, providing a smooth user experience on Hostinger.
