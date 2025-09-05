<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveAdviserRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adviser:remove-from-academe-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove adviser records from academe_accounts table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Remove adviser records from academe_accounts table
            $deletedCount = DB::table('academe_accounts')
                ->whereIn('user_id', function($query) {
                    $query->select('user_id')
                          ->from('advisers');
                })
                ->delete();
            
            $this->info("Successfully removed {$deletedCount} adviser records from academe_accounts table.");
            $this->info("Advisers now use their own table for section assignment.");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
