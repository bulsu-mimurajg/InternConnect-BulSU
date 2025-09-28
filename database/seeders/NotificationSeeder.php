<?php

namespace Database\Seeders;

use App\Models\Endorsement;
use App\Models\HTE;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating notifications for endorsements...');

        // Get all endorsements
        $endorsements = Endorsement::with(['student', 'internship.hte'])
            ->where('status', 'endorsed')
            ->get();

        $this->command->info("Found {$endorsements->count()} endorsements");

        if ($endorsements->count() === 0) {
            $this->command->info('No endorsements found. Nothing to do.');
            return;
        }

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($endorsements as $endorsement) {
            try {
                $hte = $endorsement->internship->hte;
                $user = User::find($hte->user_id);

                if (!$user) {
                    $this->command->warn("No user found for HTE {$hte->company_name} (HTE ID: {$hte->id}, user_id: {$hte->user_id})");
                    $errors++;
                    continue;
                }

                // Ensure user is verified
                if (!$user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                    $this->command->line("Verified user {$user->username} (ID: {$user->id})");
                }

                // Ensure user has HTE role
                if (!$user->hasRole('hte')) {
                    $user->assignRole('hte');
                    $this->command->line("Assigned HTE role to user {$user->username} (ID: {$user->id})");
                }

                // Check if notification already exists
                $existingNotification = Notification::where('type', 'hte_endorsement')
                    ->where('user_id', $user->id)
                    ->whereJsonContains('data->student_id', $endorsement->student_id)
                    ->whereJsonContains('data->internship_id', $endorsement->internship_id)
                    ->first();

                if ($existingNotification) {
                    $skipped++;
                    continue;
                }

                // Create notification
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'hte_endorsement',
                    'title' => 'New Student Endorsement',
                    'message' => "Student {$endorsement->student->first_name} {$endorsement->student->last_name} has been endorsed for internship at {$hte->company_name}.",
                    'data' => [
                        'hte_id' => $hte->id,
                        'student_name' => "{$endorsement->student->first_name} {$endorsement->student->last_name}",
                        'company_name' => $hte->company_name,
                        'student_id' => $endorsement->student_id,
                        'internship_id' => $endorsement->internship_id,
                    ],
                    'is_read' => false,
                ]);

                $created++;
                $this->command->line("Created notification for {$endorsement->student->first_name} {$endorsement->student->last_name} -> {$hte->company_name}");

            } catch (\Exception $e) {
                $this->command->error("Error processing endorsement {$endorsement->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->command->info("\nNotification seeding summary:");
        $this->command->info("- Created: {$created} notifications");
        $this->command->info("- Skipped (already exist): {$skipped} notifications");
        $this->command->info("- Errors: {$errors}");

        // Verify results
        $totalNotifications = Notification::where('type', 'hte_endorsement')->count();
        $this->command->info("- Total HTE endorsement notifications: {$totalNotifications}");
    }
}