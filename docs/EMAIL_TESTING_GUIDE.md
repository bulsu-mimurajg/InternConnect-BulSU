# Email Testing Guide

This guide explains how to test all email functionality in the InternCity application.

## **Email System Overview**

Your application uses:
- **Laravel Notifications** with email and database channels
- **Queued Jobs** for asynchronous email delivery
- **Custom EmailService** for PHPMailer integration
- **Multiple Notification Types**:
  - `UnifiedDeadlineNotification` - Deadline reminders
  - `HTECredentialsNotification` - HTE account credentials
  - `AdviserCredentialsNotification` - Adviser account credentials
  - `CustomResetPasswordNotification` - Password reset emails
  - `InternshipNotification` - Internship-related notifications

---

## **Method 1: Using Mailtrap (Recommended for Development)**

Mailtrap is a fake SMTP server that captures emails without sending them to real addresses.

### Setup Mailtrap

1. **Sign up** at [mailtrap.io](https://mailtrap.io) (free tier available)

2. **Get your credentials** from the Mailtrap inbox

3. **Update your `.env` file**:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@interncity.com
MAIL_FROM_NAME="InternCity System"

QUEUE_CONNECTION=database
```

4. **Start the queue worker**:
```bash
php artisan queue:work --verbose
```

5. **Test emails** - All emails will appear in your Mailtrap inbox

### Advantages:
- ✅ See actual email HTML/text rendering
- ✅ Test email formatting and styling
- ✅ Check email deliverability
- ✅ View all email headers
- ✅ Spam score analysis

---

## **Method 2: Using Log Driver (Simplest)**

Emails are written to `storage/logs/laravel.log` instead of being sent.

### Setup Log Driver

1. **Update `.env`**:
```env
MAIL_MAILER=log
MAIL_LOG_CHANNEL=stack
```

2. **Start the queue worker**:
```bash
php artisan queue:work --verbose
```

3. **View emails in logs**:
```bash
# Windows PowerShell
Get-Content -Path storage/logs/laravel.log -Wait -Tail 50

# Or open the file directly
# storage/logs/laravel.log
```

### Advantages:
- ✅ No external service required
- ✅ Quick setup
- ✅ Good for CI/CD testing

### Disadvantages:
- ❌ Can't see HTML rendering
- ❌ Harder to read email content

---

## **Method 3: Using Array Driver (For Testing)**

Emails are stored in memory (useful for automated tests).

### Setup Array Driver

1. **Update `.env`**:
```env
MAIL_MAILER=array
```

2. **Access sent emails in tests**:
```php
use Illuminate\Support\Facades\Mail;

Mail::fake();

// Trigger your email notification
$user->notify(new UnifiedDeadlineNotification($deadline, 'student'));

// Assert email was sent
Mail::assertSent(UnifiedDeadlineNotification::class, function ($mail) use ($user) {
    return $mail->hasTo($user->email);
});
```

---

## **Testing Each Notification Type**

### 1. **Test Deadline Notifications**

```bash
# Via Artisan command (if exists)
php artisan deadlines:notify

# Or via Tinker
php artisan tinker
```

```php
use App\Models\Deadline;
use App\Models\User;
use App\Notifications\UnifiedDeadlineNotification;

// Get a deadline and user
$deadline = Deadline::where('category', 'student_assessment_form')->first();
$user = User::where('role', 'student')->first();

// Send notification
$user->notify(new UnifiedDeadlineNotification($deadline, 'student', 5, null));

// Or dispatch the job
use App\Jobs\ProcessDeadlineNotificationJob;
ProcessDeadlineNotificationJob::dispatch($deadline->id, $user->id, 'student');
```

### 2. **Test HTE Credentials Email**

```php
php artisan tinker
```

```php
use App\Models\User;
use App\Notifications\HTECredentialsNotification;

$hteUser = User::where('role', 'hte')->first();
$plainPassword = 'TestPassword123'; // Use actual password from registration

$hteUser->notify(new HTECredentialsNotification($plainPassword));
```

### 3. **Test Adviser Credentials Email**

```php
php artisan tinker
```

```php
use App\Models\User;
use App\Notifications\AdviserCredentialsNotification;

$adviserUser = User::where('role', 'adviser')->first();
$plainPassword = 'TestPassword123';

$adviserUser->notify(new AdviserCredentialsNotification($plainPassword));
```

### 4. **Test Password Reset Email**

```bash
# Through the application
# Go to: http://your-app-url/forgot-password
# Enter an email address
# Check Mailtrap/logs for the reset email
```

Or via Tinker:
```php
use App\Models\User;
use Illuminate\Support\Facades\Password;

$user = User::where('email', 'test@example.com')->first();
Password::sendResetLink(['email' => $user->email]);
```

### 5. **Test Internship Notifications**

```php
php artisan tinker
```

```php
use App\Models\User;
use App\Notifications\InternshipNotification;

$student = User::where('role', 'student')->first();
$student->notify(new InternshipNotification([
    'title' => 'Placement Confirmed',
    'message' => 'Your internship placement has been confirmed.',
    'action_url' => route('student.dashboard'),
]));
```

---

## **Monitoring Queue Jobs**

### View Queue Jobs

```bash
# Check pending jobs
php artisan queue:work --once --verbose

# Monitor queue in real-time
php artisan queue:work --verbose

# Check failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {job-id}

# Retry all failed jobs
php artisan queue:retry all
```

### View Jobs in Database

```sql
-- Pending jobs
SELECT * FROM jobs ORDER BY created_at DESC;

-- Failed jobs
SELECT * FROM failed_jobs ORDER BY failed_at DESC;

-- Job batches (if using batching)
SELECT * FROM job_batches ORDER BY created_at DESC;
```

---

## **Testing All Notifications at Once**

Create a custom Artisan command to test all emails:

```bash
php artisan make:command TestAllEmails
```

```php
<?php

namespace App\Console\Commands;

use App\Models\Deadline;
use App\Models\User;
use App\Notifications\UnifiedDeadlineNotification;
use App\Notifications\HTECredentialsNotification;
use App\Notifications\AdviserCredentialsNotification;
use Illuminate\Console\Command;

class TestAllEmails extends Command
{
    protected $signature = 'test:emails {email}';
    protected $description = 'Send test emails of all types to specified email address';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Sending test emails to: {$email}");

        // Create a test user
        $testUser = User::firstOrCreate(
            ['email' => $email],
            [
                'username' => 'test_user',
                'role' => 'student',
                'password' => bcrypt('password'),
            ]
        );

        // 1. Test Deadline Notification
        $this->info('Sending deadline notification...');
        $deadline = Deadline::first();
        if ($deadline) {
            $testUser->notify(new UnifiedDeadlineNotification($deadline, 'student', 3, null));
            $this->info('✓ Deadline notification queued');
        }

        // 2. Test Credentials Notifications
        $this->info('Sending credentials notification...');
        $testUser->notify(new HTECredentialsNotification('TestPassword123'));
        $this->info('✓ Credentials notification queued');

        // 3. Test Password Reset
        $this->info('Sending password reset notification...');
        $testUser->sendPasswordResetNotification('test-token-12345');
        $this->info('✓ Password reset notification queued');

        $this->info("\nAll test emails have been queued!");
        $this->info("Make sure queue worker is running: php artisan queue:work");
        $this->info("Check your email inbox at: {$email}");

        return 0;
    }
}
```

**Usage:**
```bash
php artisan test:emails your-email@example.com
```

---

## **Debugging Email Issues**

### 1. **Check Queue Worker is Running**

```bash
# Start queue worker
php artisan queue:work --verbose

# Or use queue:listen for auto-reloading during development
php artisan queue:listen --verbose
```

### 2. **Check Email Configuration**

```bash
php artisan tinker
```

```php
// Check mail configuration
config('mail.mailer');
config('mail.from');

// Test basic email sending
use Illuminate\Support\Facades\Mail;

Mail::raw('Test email', function ($message) {
    $message->to('test@example.com')
            ->subject('Test Email');
});
```

### 3. **Check Logs**

```bash
# View application logs
tail -f storage/logs/laravel.log

# Windows PowerShell
Get-Content storage/logs/laravel.log -Wait -Tail 50
```

### 4. **Check Database Tables**

```sql
-- Check notifications table
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;

-- Check if user has email
SELECT id, email, role FROM users WHERE email IS NOT NULL;

-- Check jobs queue
SELECT * FROM jobs ORDER BY created_at DESC;

-- Check failed jobs
SELECT * FROM failed_jobs ORDER BY failed_at DESC;
```

### 5. **Common Issues & Solutions**

| Issue | Solution |
|-------|----------|
| Emails not sending | Check queue worker is running: `php artisan queue:work` |
| Queue jobs failing | Check `failed_jobs` table and logs |
| Invalid email addresses | Verify users have valid email addresses in database |
| SMTP errors | Verify SMTP credentials in `.env` file |
| Timeout errors | Increase job timeout in notification class |

---

## **Production Email Testing**

### Before Going to Production:

1. **Test with real SMTP service** (SendGrid, Mailgun, SES, etc.)
2. **Verify SPF and DKIM records** are set up correctly
3. **Test email deliverability** to different providers (Gmail, Outlook, etc.)
4. **Check spam score** using Mail Tester
5. **Set up monitoring** for failed jobs and bounced emails
6. **Configure queue workers** on production server
7. **Set up proper error notifications** for failed emails

### Production SMTP Setup Example (SendGrid):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="InternCity System"

QUEUE_CONNECTION=database
```

---

## **Email Templates Location**

All email templates are located in:
```
resources/views/emails/
├── unified-deadline.blade.php
├── hte-credentials.blade.php
├── adviser-credentials.blade.php
└── reset-password.blade.php
```

To preview email templates without sending:
```php
php artisan tinker
```

```php
use App\Models\Deadline;

$deadline = Deadline::first();
$urgencyLevel = ['level' => 'high', 'is_critical' => true, 'title' => 'Test', 'message' => 'Test message'];

$html = view('emails.unified-deadline', [
    'urgencyLevel' => $urgencyLevel,
    'userDisplayName' => 'Test User',
    'deadlineName' => $deadline->title,
    'categoryDisplay' => 'Student Assessment',
    'deadlineDate' => $deadline->end_date,
    'timeRemainingText' => 'due in 3 days',
    'daysRemaining' => 3,
    'hoursRemaining' => null,
    'actionText' => 'Complete your assessment',
    'actionUrl' => 'http://localhost/student/dashboard',
    'roleSpecificContent' => 'Test content',
])->render();

// Save to file to preview in browser
file_put_contents('email-preview.html', $html);
echo "Preview saved to email-preview.html";
```

---

## **Quick Reference**

### Start Testing:
```bash
# 1. Set up Mailtrap or log driver in .env
# 2. Start queue worker
php artisan queue:work --verbose

# 3. Test all emails
php artisan test:emails your-email@example.com

# 4. Check Mailtrap inbox or logs
tail -f storage/logs/laravel.log
```

### Monitor Queue:
```bash
# Watch queue processing
php artisan queue:work --verbose

# Check failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Clear all jobs
php artisan queue:flush
```

### Debugging:
```bash
# View logs
tail -f storage/logs/laravel.log

# Test configuration
php artisan tinker
> config('mail')

# Clear cache if needed
php artisan config:clear
php artisan cache:clear
```

---

## **Next Steps**

1. ✅ Set up Mailtrap account and configure `.env`
2. ✅ Create the `TestAllEmails` command
3. ✅ Run `php artisan test:emails your-email@example.com`
4. ✅ Start queue worker with `php artisan queue:work`
5. ✅ Check your Mailtrap inbox for all test emails
6. ✅ Review email formatting and content
7. ✅ Fix any issues and test again

Need help with any specific notification? Let me know!

