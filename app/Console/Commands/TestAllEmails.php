<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TestAllEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:all-emails {email : The email address to send test emails to} {--force-smtp : Force SMTP mode for testing (requires SMTP config)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all email templates to a specified email address for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Validate email address
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            $this->error('Invalid email address provided.');
            return 1;
        }

        // Check current mail configuration
        $currentMailer = config('mail.default');
        $this->info("Current mail driver: {$currentMailer}");
        
        // Handle force SMTP option
        if ($this->option('force-smtp')) {
            config(['mail.default' => 'smtp']);
            $this->info('Forced SMTP mode enabled');
        }
        
        if ($currentMailer === 'log' && !$this->option('force-smtp')) {
            $this->warn('⚠️  WARNING: Mail driver is set to "log" - emails will be written to storage/logs/laravel.log instead of being sent!');
            $this->newLine();
            $this->info('To send real emails, you can:');
            $this->line('1. Update your .env file with SMTP settings:');
            $this->line('   MAIL_MAILER=smtp');
            $this->line('   MAIL_HOST=your-smtp-host');
            $this->line('   MAIL_PORT=587');
            $this->line('   MAIL_USERNAME=your-email@domain.com');
            $this->line('   MAIL_PASSWORD=your-password');
            $this->line('   MAIL_ENCRYPTION=tls');
            $this->newLine();
            $this->line('2. Or use --force-smtp flag (requires SMTP config in .env)');
            $this->newLine();
            
            if (!$this->confirm('Continue with log driver (emails will be logged, not sent)?')) {
                $this->info('Command cancelled. Please configure SMTP settings and try again.');
                return 0;
            }
        }

        $this->info("Sending all email templates to: {$email}");
        $this->newLine();

        $emails = [
            'Account Verification' => $this->sendAccountVerification($email),
            'Adviser Credentials' => $this->sendAdviserCredentials($email),
            'Assessment Reminder' => $this->sendAssessmentReminder($email),
            'HTE Credentials' => $this->sendHTECredentials($email),
            'Internship Notification' => $this->sendInternshipNotification($email),
            'Password Reset' => $this->sendPasswordReset($email),
            'Unified Deadline' => $this->sendUnifiedDeadline($email),
        ];

        $this->newLine();
        $this->info('Email sending summary:');
        
        foreach ($emails as $name => $success) {
            $status = $success ? '✅ Sent' : '❌ Failed';
            $this->line("  {$name}: {$status}");
        }

        $successCount = array_sum($emails);
        $totalCount = count($emails);
        
        $this->newLine();
        if ($successCount === $totalCount) {
            $this->info("🎉 All {$totalCount} emails sent successfully!");
        } else {
            $this->warn("⚠️  {$successCount}/{$totalCount} emails sent successfully.");
        }

        return 0;
    }

    private function sendAccountVerification($email): bool
    {
        try {
            Mail::send('emails.account-verification', [
                'userName' => 'Test User',
                'verificationUrl' => 'https://example.com/verify?token=test123',
                'headerSubtitle' => 'Account Verification Test'
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('BULSU InternConnect - Account Verification Test');
            });
            return true;
        } catch (\Exception $e) {
            $this->error("Account Verification failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendAdviserCredentials($email): bool
    {
        try {
            Mail::send('emails.adviser-credentials', [
                'adviserName' => 'Dr. Test Adviser',
                'username' => 'test_adviser',
                'password' => 'TempPassword123',
                'email' => $email,
                'loginUrl' => 'https://example.com/login',
                'sections' => ['CS-3A', 'CS-3B', 'IT-3A'],
                'headerSubtitle' => 'Adviser Account Test'
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('BULSU InternConnect - Adviser Credentials Test');
            });
            return true;
        } catch (\Exception $e) {
            $this->error("Adviser Credentials failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendAssessmentReminder($email): bool
    {
        try {
            Mail::send('emails.assessment-reminder', [
                'studentName' => 'Test Student',
                'assessmentType' => 'Technical Skills Assessment',
                'dashboardUrl' => 'https://example.com/dashboard',
                'headerSubtitle' => 'Assessment Reminder Test'
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('BULSU InternConnect - Assessment Reminder Test');
            });
            return true;
        } catch (\Exception $e) {
            $this->error("Assessment Reminder failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendHTECredentials($email): bool
    {
        try {
            Mail::send('emails.hte-credentials', [
                'companyName' => 'Test Company Inc.',
                'username' => 'test_hte',
                'password' => 'TempPassword123',
                'email' => $email,
                'loginUrl' => 'https://example.com/login',
                'headerSubtitle' => 'HTE Account Test'
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('BULSU InternConnect - HTE Credentials Test');
            });
            return true;
        } catch (\Exception $e) {
            $this->error("HTE Credentials failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendInternshipNotification($email): bool
    {
        try {
            Mail::send('emails.internship-notification', [
                'studentName' => 'Test Student',
                'internshipDetails' => [
                    'company_name' => 'Test Company Inc.',
                    'position' => 'Software Developer Intern',
                    'duration' => '6 months',
                    'start_date' => 'January 15, 2024'
                ],
                'headerSubtitle' => 'Internship Placement Test'
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('BULSU InternConnect - Internship Notification Test');
            });
            return true;
        } catch (\Exception $e) {
            $this->error("Internship Notification failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendPasswordReset($email): bool
    {
        try {
            Mail::send('emails.password-reset', [
                'userName' => 'Test User',
                'resetUrl' => 'https://example.com/reset?token=test123',
                'headerSubtitle' => 'Password Reset Test'
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('BULSU InternConnect - Password Reset Test');
            });
            return true;
        } catch (\Exception $e) {
            $this->error("Password Reset failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendUnifiedDeadline($email): bool
    {
        try {
            Mail::send('emails.unified-deadline', [
                'userDisplayName' => 'Test User',
                'urgencyLevel' => ['message' => 'This is a test deadline notification.'],
                'deadlineName' => 'Test Assessment Submission',
                'categoryDisplay' => 'Student Assessment',
                'deadlineDate' => now()->addDays(3),
                'timeRemainingText' => '3 days remaining',
                'daysRemaining' => 3,
                'hoursRemaining' => null,
                'actionText' => 'Please complete your test assessment before the deadline.',
                'roleSpecificContent' => 'As a test user, ensure all required fields are completed.',
                'actionUrl' => 'https://example.com/assessment',
                'headerSubtitle' => 'Deadline Notification Test'
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('BULSU InternConnect - Deadline Notification Test');
            });
            return true;
        } catch (\Exception $e) {
            $this->error("Unified Deadline failed: " . $e->getMessage());
            return false;
        }
    }
}
