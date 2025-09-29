<?php

namespace App\Console\Commands;

use App\Services\CentralizedDeadlineNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessDeadlineNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deadlines:process-notifications 
                            {--user-id= : Process notifications for a specific user ID}
                            {--role= : Process notifications for a specific role}
                            {--cleanup : Only cleanup old notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process deadline notifications for all users or specific users/roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting deadline notification processing...');

        try {
            $service = new CentralizedDeadlineNotificationService();

            if ($this->option('cleanup')) {
                $this->info('Cleaning up old deadline notifications...');
                $service->cleanupOldDeadlineNotifications();
                $this->info('Cleanup completed successfully.');
                return;
            }

            if ($userId = $this->option('user-id')) {
                $this->processForUser($service, $userId);
            } elseif ($role = $this->option('role')) {
                $this->processForRole($service, $role);
            } else {
                $this->processForAll($service);
            }

            $this->info('Deadline notification processing completed successfully.');

        } catch (\Exception $e) {
            $this->error('Failed to process deadline notifications: ' . $e->getMessage());
            Log::error('Deadline notification processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Process notifications for all users
     */
    private function processForAll(CentralizedDeadlineNotificationService $service): void
    {
        $this->info('Processing deadline notifications for all users...');
        $service->checkAndCreateDeadlineNotifications();
        $this->info('Processed notifications for all users.');
    }

    /**
     * Process notifications for a specific user
     */
    private function processForUser(CentralizedDeadlineNotificationService $service, string $userId): void
    {
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return;
        }

        $this->info("Processing deadline notifications for user: {$user->username} (ID: {$userId})");
        $service->processDeadlineNotificationsForUser($user);
        $this->info("Processed notifications for user: {$user->username}");
    }

    /**
     * Process notifications for a specific role
     */
    private function processForRole(CentralizedDeadlineNotificationService $service, string $role): void
    {
        $users = \App\Models\User::role($role)->get();
        
        if ($users->isEmpty()) {
            $this->warn("No users found with role: {$role}");
            return;
        }

        $this->info("Processing deadline notifications for {$users->count()} users with role: {$role}");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $service->processDeadlineNotificationsForUser($user);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Processed notifications for {$users->count()} users with role: {$role}");
    }
}
