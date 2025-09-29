<?php

namespace App\Jobs;

use App\Models\Deadline;
use App\Models\User;
use App\Services\CentralizedDeadlineNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDeadlineNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3; // Retry up to 3 times
    public $backoff = [30, 60, 120]; // Wait 30s, 1m, 2m between retries

    protected $deadlineId;
    protected $userId;
    protected $userRole;

    /**
     * Create a new job instance.
     */
    public function __construct(int $deadlineId, int $userId, string $userRole)
    {
        $this->deadlineId = $deadlineId;
        $this->userId = $userId;
        $this->userRole = $userRole;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Processing deadline notification job', [
                'deadline_id' => $this->deadlineId,
                'user_id' => $this->userId,
                'user_role' => $this->userRole,
            ]);

            // Get the deadline and user
            $deadline = Deadline::find($this->deadlineId);
            $user = User::find($this->userId);

            if (!$deadline || !$user) {
                Log::warning('Deadline or user not found for notification job', [
                    'deadline_id' => $this->deadlineId,
                    'user_id' => $this->userId,
                ]);
                return;
            }

            // Create the centralized service instance
            $service = new CentralizedDeadlineNotificationService();
            
            // Process the notification for this specific user and specific deadline
            $service->processDeadlineNotificationForUserAndDeadline($user, $deadline, $this->userRole);

            Log::info('Deadline notification job completed successfully', [
                'deadline_id' => $this->deadlineId,
                'user_id' => $this->userId,
                'user_role' => $this->userRole,
            ]);

        } catch (\Exception $e) {
            Log::error('Deadline notification job failed', [
                'deadline_id' => $this->deadlineId,
                'user_id' => $this->userId,
                'user_role' => $this->userRole,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Deadline notification job permanently failed', [
            'deadline_id' => $this->deadlineId,
            'user_id' => $this->userId,
            'user_role' => $this->userRole,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}