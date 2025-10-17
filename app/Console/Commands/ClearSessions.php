<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ClearSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:clear {--all : Clear all sessions including current user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all user sessions to resolve CSRF token issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing user sessions...');

        try {
            if ($this->option('all')) {
                // Clear all sessions from database
                if (config('session.driver') === 'database') {
                    DB::table('sessions')->truncate();
                    $this->info('All database sessions cleared.');
                } else {
                    // Clear file sessions
                    $sessionPath = storage_path('framework/sessions');
                    if (is_dir($sessionPath)) {
                        $files = glob($sessionPath . '/*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                unlink($file);
                            }
                        }
                        $this->info('All file sessions cleared.');
                    }
                }
            } else {
                // Clear expired sessions only
                if (config('session.driver') === 'database') {
                    $deleted = DB::table('sessions')
                        ->where('last_activity', '<', now()->subMinutes(config('session.lifetime')))
                        ->delete();
                    $this->info("Cleared {$deleted} expired sessions.");
                }
            }

            $this->info('Session clearing completed successfully!');
            $this->warn('Note: Users will need to log in again after this operation.');
            
        } catch (\Exception $e) {
            $this->error('Error clearing sessions: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
