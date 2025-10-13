<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\StudentArchiveService;
use App\Services\InternshipSeasonService;
use Illuminate\Support\Facades\Log;

class ArchiveSeasonStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $seasonId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        StudentArchiveService $archiveService,
        InternshipSeasonService $seasonService
    ): void {
        Log::info('Starting ArchiveSeasonStudentsJob', [
            'season_id' => $this->seasonId,
        ]);

        try {
            // Archive all students in the season
            $archivedCount = $archiveService->archiveStudentsBySeason($this->seasonId);

            // Complete the season
            $seasonService->completeSeason($this->seasonId);

            Log::info('ArchiveSeasonStudentsJob completed successfully', [
                'season_id' => $this->seasonId,
                'archived_count' => $archivedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('ArchiveSeasonStudentsJob failed', [
                'season_id' => $this->seasonId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ArchiveSeasonStudentsJob failed permanently', [
            'season_id' => $this->seasonId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}