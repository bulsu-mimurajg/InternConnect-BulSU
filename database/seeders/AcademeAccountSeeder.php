<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AcademeAccount;
use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademeAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students with their student records
        $students = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })->with('student')->get();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($students as $student) {
            if ($student->student) {
                // Use the section_id from the student record to maintain consistency
                $sectionId = $student->student->section_id;
                
                $academeAccount = AcademeAccount::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'section_id' => $sectionId,
                    ],
                    [
                        'user_id' => $student->id,
                        'section_id' => $sectionId,
                    ]
                );
                
                if ($academeAccount->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }
            }
        }

        $this->command->info("AcademeAccount seeder completed!");
        $this->command->info("Created: {$createdCount} accounts");
        $this->command->info("Updated: {$updatedCount} accounts");
    }
}
