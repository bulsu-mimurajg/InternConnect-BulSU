<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\StudentMatch;
use App\Models\UnplacedStudent;
use Illuminate\Database\Seeder;

class UnplacedStudentDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder removes compatibility scores for demo unplaced students
     * and ensures they appear in the unplaced students list.
     */
    public function run(): void
    {
        $this->command->info('Setting up demo unplaced students...');

        // Student numbers for our demo unplaced students
        $demoUnplacedStudents = [
            '2022100118', // Alice Johnson
            '2022100119', // Robert Chen
            '2022100120', // Maria Rodriguez
            '2022100121', // David Patel
            '2022100122', // Jennifer Kim
        ];

        foreach ($demoUnplacedStudents as $studentNumber) {
            $student = Student::where('student_number', $studentNumber)->first();
            
            if ($student) {
                // Remove all compatibility scores for this student
                StudentMatch::where('student_id', $student->id)->delete();
                
                $this->command->info("  - Removed compatibility scores for {$student->first_name} {$student->last_name}");
                
                // Ensure UnplacedStudent record exists
                $unplacedRecord = UnplacedStudent::where('student_id', $student->id)->first();
                if (!$unplacedRecord) {
                    UnplacedStudent::create([
                        'student_id' => $student->id,
                        'reason' => 'All matches have no available slots',
                        'requires_manual_intervention' => true,
                        'notes' => 'Demo student for testing unplaced functionality',
                    ]);
                    $this->command->info("  - Created UnplacedStudent record for {$student->first_name} {$student->last_name}");
                }
            }
        }

        $this->command->info('Demo unplaced students setup complete!');
    }
}
