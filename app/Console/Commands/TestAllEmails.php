<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;
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

        $this->info("Sending all email templates to: {$email} using PHPMailer EmailService");
        $this->info("From: " . env('PHPMAILER_FROM_EMAIL', 'internconnectbulsu@gmail.com'));
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
            $emailService = new EmailService();
            $subject = "BULSU InternConnect - Account Verification Test";
            
            $body = view('emails.account-verification', [
                'userName' => 'Test User',
                'verificationUrl' => 'https://example.com/verify?token=test123',
                'headerSubtitle' => 'Account Verification Test'
            ])->render();
            
            $emailService->sendEmail($email, $subject, $body, 'Test User');
            return true;
        } catch (\Exception $e) {
            $this->error("Account Verification failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendAdviserCredentials($email): bool
    {
        try {
            $emailService = new EmailService();
            $subject = "BULSU InternConnect - Adviser Credentials Test";
            
            $body = view('emails.adviser-credentials', [
                'adviserName' => 'Dr. Test Adviser',
                'username' => 'test_adviser',
                'password' => 'TempPassword123',
                'email' => $email,
                'loginUrl' => 'https://example.com/login',
                'sections' => ['CS-3A', 'CS-3B', 'IT-3A'],
                'headerSubtitle' => 'Adviser Account Test'
            ])->render();
            
            $emailService->sendEmail($email, $subject, $body, 'Dr. Test Adviser');
            return true;
        } catch (\Exception $e) {
            $this->error("Adviser Credentials failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendAssessmentReminder($email): bool
    {
        try {
            $emailService = new EmailService();
            $subject = "BULSU InternConnect - Assessment Reminder Test";
            
            $body = view('emails.assessment-reminder', [
                'studentName' => 'Test Student',
                'assessmentType' => 'Technical Skills Assessment',
                'dashboardUrl' => 'https://example.com/dashboard',
                'headerSubtitle' => 'Assessment Reminder Test'
            ])->render();
            
            $emailService->sendEmail($email, $subject, $body, 'Test Student');
            return true;
        } catch (\Exception $e) {
            $this->error("Assessment Reminder failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendHTECredentials($email): bool
    {
        try {
            $emailService = new EmailService();
            $subject = "BULSU InternConnect - HTE Credentials Test";
            
            $body = view('emails.hte-credentials', [
                'companyName' => 'Test Company Inc.',
                'username' => 'test_hte',
                'password' => 'TempPassword123',
                'email' => $email,
                'loginUrl' => 'https://example.com/login',
                'headerSubtitle' => 'HTE Account Test'
            ])->render();
            
            $emailService->sendEmail($email, $subject, $body, 'Test Company Inc.');
            return true;
        } catch (\Exception $e) {
            $this->error("HTE Credentials failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendInternshipNotification($email): bool
    {
        try {
            $emailService = new EmailService();
            $subject = "BULSU InternConnect - Internship Notification Test";
            
            $body = view('emails.internship-notification', [
                'studentName' => 'Test Student',
                'internshipDetails' => [
                    'company_name' => 'Test Company Inc.',
                    'position' => 'Software Developer Intern',
                    'duration' => '6 months',
                    'start_date' => 'January 15, 2024'
                ],
                'headerSubtitle' => 'Internship Placement Test'
            ])->render();
            
            $emailService->sendEmail($email, $subject, $body, 'Test Student');
            return true;
        } catch (\Exception $e) {
            $this->error("Internship Notification failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendPasswordReset($email): bool
    {
        try {
            $emailService = new EmailService();
            $subject = "BULSU InternConnect - Password Reset Test";
            
            $body = view('emails.password-reset', [
                'userName' => 'Test User',
                'resetUrl' => 'https://example.com/reset?token=test123',
                'headerSubtitle' => 'Password Reset Test'
            ])->render();
            
            $emailService->sendEmail($email, $subject, $body, 'Test User');
            return true;
        } catch (\Exception $e) {
            $this->error("Password Reset failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendUnifiedDeadline($email): bool
    {
        try {
            $emailService = new EmailService();
            $subject = "BULSU InternConnect - Deadline Notification Test";
            
            $body = view('emails.unified-deadline', [
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
            ])->render();
            
            $emailService->sendEmail($email, $subject, $body, 'Test User');
            return true;
        } catch (\Exception $e) {
            $this->error("Unified Deadline failed: " . $e->getMessage());
            return false;
        }
    }
}
