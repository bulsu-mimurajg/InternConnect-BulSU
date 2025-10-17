<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\AcademeAccount;
use App\Models\Section;
use Illuminate\Console\Command;

class FixUserSectionAssignment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:fix-section {username} {--section-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix section assignment for a user by creating missing academe_accounts record';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $sectionId = $this->option('section-id');

        // Find the user
        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User '{$username}' not found!");
            return 1;
        }

        $this->info("Found user: {$user->username} (ID: {$user->id})");
        $this->info("User roles: " . $user->roles->pluck('name')->implode(', '));

        // Check if user already has academe accounts
        $existingAccounts = $user->academeAccounts;
        $this->info("Existing academe accounts: " . $existingAccounts->count());

        if ($existingAccounts->count() > 0) {
            $this->info("User already has section assignments:");
            foreach ($existingAccounts as $account) {
                $this->line("- Section ID: {$account->section_id}, Section Name: {$account->section->section_name}");
            }
            return 0;
        }

        // Get available sections
        $sections = Section::where('status', 'active')->get();
        $this->info("Available sections:");
        foreach ($sections as $section) {
            $this->line("- ID: {$section->section_id}, Name: {$section->section_name}");
        }

        // Determine which section to assign
        $targetSection = null;
        
        if ($sectionId) {
            $targetSection = Section::where('section_id', $sectionId)->where('status', 'active')->first();
            if (!$targetSection) {
                $this->error("Section with ID {$sectionId} not found or not active!");
                return 1;
            }
        } else {
            // Assign to first available section
            $targetSection = $sections->first();
        }

        if (!$targetSection) {
            $this->error("No active sections found!");
            return 1;
        }

        // Create academe account
        AcademeAccount::create([
            'user_id' => $user->id,
            'section_id' => $targetSection->section_id,
        ]);

        $this->info("Successfully assigned user to section: {$targetSection->section_name}");
        $this->info("Fix completed successfully!");

        return 0;
    }
}
