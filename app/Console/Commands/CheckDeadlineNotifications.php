<?php

namespace App\Console\Commands;

use App\Services\DeadlineNotificationService;
use Illuminate\Console\Command;

class CheckDeadlineNotifications extends Command
{
    protected $signature = 'deadlines:check-notifications';
    protected $description = 'Check and create/update deadline notifications for HTE users';

    public function handle()
    {
        $this->info('Checking deadline notifications...');

        $service = new DeadlineNotificationService();
        
        // Clean up old notifications first
        $this->info('Cleaning up old deadline notifications...');
        $service->cleanupOldDeadlineNotifications();
        
        // Check and create/update notifications
        $this->info('Checking for deadline notifications...');
        $service->checkAndCreateDeadlineNotifications();
        
        $this->info('Deadline notification check completed!');
        
        return 0;
    }
}