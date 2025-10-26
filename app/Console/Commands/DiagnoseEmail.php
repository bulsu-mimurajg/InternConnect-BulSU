<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class DiagnoseEmail extends Command
{
    protected $signature = 'email:diagnose {email}';
    protected $description = 'Diagnose email configuration and send test email';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('🔍 Diagnosing Email Configuration...');
        $this->newLine();

        // 1. Check queue configuration
        $this->info('1. Queue Configuration:');
        $queueConnection = config('queue.default');
        $this->line("   Queue Driver: {$queueConnection}");
        
        if ($queueConnection === 'database') {
            $jobCount = DB::table('jobs')->count();
            $this->line("   Jobs in queue: {$jobCount}");
        }
        $this->newLine();

        // 2. Check mail configuration
        $this->info('2. Mail Configuration:');
        $mailDriver = config('mail.default');
        $this->line("   Mail Driver: {$mailDriver}");
        $this->line("   From Address: " . config('mail.from.address'));
        $this->newLine();

        // 3. Check PHPMailer
        $this->info('3. PHPMailer Check:');
        try {
            $emailService = new EmailService();
            $this->line("   ✅ EmailService instantiated successfully");
        } catch (\Exception $e) {
            $this->error("   ❌ EmailService failed: " . $e->getMessage());
            return 1;
        }
        $this->newLine();

        // 4. Test email sending
        $this->info('4. Testing Email Sending:');
        try {
            $emailService->sendEmail(
                $email,
                'Email Test - ' . now()->format('Y-m-d H:i:s'),
                '<h1>Test Email</h1><p>This is a test email from BULSU InternConnect.</p><p>If you receive this, email is working correctly!</p>',
                'Test User'
            );
            $this->line("   ✅ Test email sent successfully to {$email}");
        } catch (\Exception $e) {
            $this->error("   ❌ Failed to send test email: " . $e->getMessage());
            return 1;
        }
        $this->newLine();

        // 5. Check queue processing
        if ($queueConnection === 'database') {
            $this->info('5. Queue Processing:');
            $this->line("   To process queued emails, run: php artisan queue:work");
            $this->line("   Or check jobs table: DB::table('jobs')->get()");
        }

        $this->newLine();
        $this->info('✅ Email diagnosis complete!');
        
        return 0;
    }
}

