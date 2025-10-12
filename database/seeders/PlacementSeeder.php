<?php

namespace Database\Seeders;

use App\Models\StudentMatch;
use App\Models\Student;
use App\Models\Internship;
use Illuminate\Database\Seeder;

class PlacementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some students and internships for sample placements
        $students = Student::where('is_submit', true)->take(3)->get();
        $internships = Internship::where('is_active', true)->take(3)->get();

        foreach ($students as $index => $student) {
            if (isset($internships[$index])) {
                // Use updateOrCreate to prevent duplicates
                StudentMatch::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'internship_id' => $internships[$index]->id,
                    ],
                    [
                        'compatibility_score' => rand(70, 95),
                        'rank' => 1, // Top rank for sample
                        'endorsement_status' => 'pending',
                        'placement_status' => 'pending',
                    ]
                );
            }
        }
    }
}
