<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\SubCategory;
use App\Models\StudentScore;
use Illuminate\Database\Seeder;

class StudentScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students and subcategories
        $students = Student::all();
        $subcategories = SubCategory::all();

        if ($students->isEmpty() || $subcategories->isEmpty()) {
            $this->command->warn('No students or subcategories found. Please run StudentSeeder and CategorySeeder first.');
            return;
        }

        // Define score ranges for different skill levels (1-5 scale)
        $scoreRanges = [
            'excellent' => [4.0, 5.0],
            'good' => [3.5, 3.99],
            'average' => [3.0, 3.49],
            'below_average' => [2.0, 2.99],
            'poor' => [1.0, 1.99]
        ];

        // Define skill level distribution for realistic data
        $skillDistribution = [
            'excellent' => 0.15,  // 15% excellent
            'good' => 0.35,       // 35% good
            'average' => 0.30,    // 30% average
            'below_average' => 0.15, // 15% below average
            'poor' => 0.05        // 5% poor
        ];

        foreach ($students as $student) {
            foreach ($subcategories as $subcategory) {
                // Generate a random score between 1 and 5 with more variation
                $score = round(mt_rand(100, 500) / 100, 2);
                
                // Ensure score is within valid range (1-5)
                $score = max(1, min(5, $score));
                


                // Add some variation based on subcategory type
                $categoryName = $subcategory->category->category_name;
                $subcategoryName = $subcategory->subcategory_name;

                // Adjust scores based on category for more realistic data
                if ($categoryName === 'Language Proficiency') {
                    // Programming languages - vary between 2.5 and 5.0
                    $score = round(mt_rand(250, 500) / 100, 2);
                } elseif ($categoryName === 'Technical Skill') {
                    // Technical skills - vary between 2.0 and 4.5
                    $score = round(mt_rand(200, 450) / 100, 2);
                } elseif ($categoryName === 'Soft Skill') {
                    // Soft skills - vary between 3.0 and 5.0
                    $score = round(mt_rand(300, 500) / 100, 2);
                }

                // Ensure score is within valid range (1-5)
                $score = max(1, min(5, $score));

                // Create or update student score
                StudentScore::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'sub_category_id' => $subcategory->id,
                    ],
                    [
                        'score' => $score,
                    ]
                );
            }
        }

        // Create some standout students for visualization purposes
        $this->createStandoutStudents($students, $subcategories);

        // Mark all students as having submitted assessments since we're creating scores for them
        foreach ($students as $student) {
            $student->update(['is_submit' => true]);
        }

        $this->command->info('Marked all students as having submitted assessments.');
    }

    /**
     * Create some students with standout scores for better visualization
     */
    private function createStandoutStudents($students, $subcategories)
    {
        // Get a few students to make them stand out
        $standoutStudents = $students->take(3);
        $programmingSubcategories = $subcategories->whereIn('subcategory_name', ['Java', 'Python', 'JavaScript']);
        $softSkillSubcategories = $subcategories->whereIn('subcategory_name', ['Communication Skills', 'Problem-Solving and Analytical Skills']);

        foreach ($standoutStudents as $index => $student) {
            if ($index === 0) {
                // First student: Excellent in programming, good in soft skills
                foreach ($programmingSubcategories as $subcategory) {
                    StudentScore::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'sub_category_id' => $subcategory->id,
                        ],
                        [
                            'score' => round(4.5 + (mt_rand(0, 50) / 100), 2),
                        ]
                    );
                }
                foreach ($softSkillSubcategories as $subcategory) {
                    StudentScore::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'sub_category_id' => $subcategory->id,
                        ],
                        [
                            'score' => round(4.0 + (mt_rand(0, 100) / 100), 2),
                        ]
                    );
                }
            } elseif ($index === 1) {
                // Second student: Good in programming, excellent in soft skills
                foreach ($programmingSubcategories as $subcategory) {
                    StudentScore::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'sub_category_id' => $subcategory->id,
                        ],
                        [
                            'score' => round(3.5 + (mt_rand(0, 100) / 100), 2),
                        ]
                    );
                }
                foreach ($softSkillSubcategories as $subcategory) {
                    StudentScore::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'sub_category_id' => $subcategory->id,
                        ],
                        [
                            'score' => round(4.5 + (mt_rand(0, 50) / 100), 2),
                        ]
                    );
                }
            } elseif ($index === 2) {
                // Third student: Balanced but above average
                foreach ($subcategories as $subcategory) {
                    StudentScore::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'sub_category_id' => $subcategory->id,
                        ],
                        [
                            'score' => round(4.0 + (mt_rand(0, 100) / 100), 2),
                        ]
                    );
                }
            }
        }
    }
}
