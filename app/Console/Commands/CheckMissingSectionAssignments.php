<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\AcademeAccount;
use Illuminate\Console\Command;

class CheckMissingSectionAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check-missing-sections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for users who are missing section assignments in academe_accounts table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for users with missing section assignments...");

        // Get all users with adviser or student roles
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['adviser', 'student']);
        })->get();

        $missingAssignments = [];

        foreach ($users as $user) {
            $academeAccounts = $user->academeAccounts;
            
            if ($academeAccounts->count() === 0) {
                $missingAssignments[] = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->implode(', '),
                ];
            }
        }

        if (count($missingAssignments) > 0) {
            $this->warn("Found " . count($missingAssignments) . " users with missing section assignments:");
            
            $headers = ['ID', 'Username', 'Email', 'Roles'];
            $rows = array_map(function ($user) {
                return [
                    $user['id'],
                    $user['username'],
                    $user['email'],
                    $user['roles'],
                ];
            }, $missingAssignments);
            
            $this->table($headers, $rows);
            
            $this->info("\nTo fix these users, run:");
            foreach ($missingAssignments as $user) {
                $this->line("php artisan user:fix-section {$user['username']}");
            }
        } else {
            $this->info("All users have proper section assignments!");
        }

        return 0;
    }
}
