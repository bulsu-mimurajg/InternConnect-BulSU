<?php

namespace App\Console\Commands;

use App\Services\StudentDeadlineNotificationService;
use Illuminate\Console\Command;

class CheckStudentDeadlineNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'student-deadlines:check-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and create/update student deadline notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking student deadline notifications...');
        
        $service = new StudentDeadlineNotificationService();
        
        // Check and create/update deadline notifications
        $service->checkAndCreateDeadlineNotifications();
        
        // Clean up old notifications
        $service->cleanupOldDeadlineNotifications();
        
        $this->info('Student deadline notifications check completed.');
        
        return 0;
    }
}