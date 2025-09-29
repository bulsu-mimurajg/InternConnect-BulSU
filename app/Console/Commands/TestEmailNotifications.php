<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\HTECredentialsNotification;
use App\Notifications\AdviserCredentialsNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestEmailNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-notifications {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test HTE and Adviser email notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        // Create a temporary user for testing
        $testUser = new User([
            'email' => $email,
            'username' => 'test_user',
        ]);

        $this->info('Testing HTE Credentials Notification with Blade template...');
        
        // Test HTE notification
        try {
            $testUser->notify(new HTECredentialsNotification(
                'test_hte_username',
                'test_password123',
                'Test Company Name'
            ));
            $this->info('✅ HTE credentials notification sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ HTE credentials notification failed: ' . $e->getMessage());
        }

        $this->info('Testing Adviser Credentials Notification with Blade template...');
        
        // Test Adviser notification
        try {
            $testUser->notify(new AdviserCredentialsNotification(
                'test_adviser_username',
                'test_password123',
                'Test Adviser Name',
                ['CS-3A', 'CS-3B', 'IT-3A']
            ));
            $this->info('✅ Adviser credentials notification sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Adviser credentials notification failed: ' . $e->getMessage());
        }

        $this->info('Testing Password Reset Notification with Blade template...');
        
        // Test Password Reset notification
        try {
            $testUser->notify(new \App\Notifications\CustomResetPasswordNotification('test_token'));
            $this->info('✅ Password reset notification sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Password reset notification failed: ' . $e->getMessage());
        }

        $this->info('Testing Student Deadline Notification with Blade template...');
        
        // Test Student Deadline notification
        try {
            $testUser->notify(new \App\Notifications\StudentDeadlineNotification([
                'deadline_name' => 'Student Assessment',
                'deadline_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'category' => 'student_assessment_form',
            ], 3, null));
            $this->info('✅ Student deadline notification sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Student deadline notification failed: ' . $e->getMessage());
        }

        $this->info('Testing HTE Deadline Notification with Blade template...');
        
        // Test HTE Deadline notification
        try {
            $testUser->notify(new \App\Notifications\HTEDeadlineNotification([
                'deadline_name' => 'HTE Assessment Form',
                'deadline_date' => now()->addDays(5)->format('M d, Y \a\t g:i A'),
                'category' => 'hte_assessment_form',
            ], 5, null));
            $this->info('✅ HTE deadline notification sent successfully!');
        } catch (\Exception $e) {
            $this->error('❌ HTE deadline notification failed: ' . $e->getMessage());
        }

        $this->info('Testing EmailService with Blade templates...');
        
        // Test EmailService methods
        try {
            $emailService = new \App\Services\EmailService();
            
            // Test internship notification
            $emailService->sendInternshipNotification(
                $email,
                'Test Student',
                [
                    'company_name' => 'Test Company',
                    'position' => 'Software Developer Intern',
                    'duration' => '6 months',
                    'start_date' => '2024-01-15'
                ]
            );
            $this->info('✅ Internship notification sent successfully!');
            
            // Test assessment reminder
            $emailService->sendAssessmentReminder(
                $email,
                'Test Student',
                'Technical Assessment'
            );
            $this->info('✅ Assessment reminder sent successfully!');
            
            // Test account verification
            $emailService->sendAccountVerification(
                $email,
                'Test User',
                'test_verification_token'
            );
            $this->info('✅ Account verification sent successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ EmailService test failed: ' . $e->getMessage());
        }

        $this->info('All email notification tests completed!');
        $this->info('Check your email inbox for the test messages with new Blade templates.');
    }
}
