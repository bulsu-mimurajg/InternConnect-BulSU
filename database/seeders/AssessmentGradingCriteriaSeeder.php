<?php

namespace Database\Seeders;

use App\Models\AssessmentGradingCriteria;
use Illuminate\Database\Seeder;

class AssessmentGradingCriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Assessment Grading Criteria...');

        // Global grading criteria (applies to all assessments if no specific criteria exists)
        $globalCriteria = [
            [
                'min_score' => 95.00,
                'max_score' => 100.00,
                'grade_label' => 'Excellent',
                'grade_code' => 'A+',
                'grade_point' => 4.00,
                'description' => 'Outstanding performance demonstrating comprehensive understanding and mastery of all concepts.',
                'feedback_template' => 'Excellent work! You have demonstrated outstanding mastery with a score of {score}%.',
                'color_code' => '#4CAF50',
                'display_order' => 1,
            ],
            [
                'min_score' => 90.00,
                'max_score' => 94.99,
                'grade_label' => 'Very Good',
                'grade_code' => 'A',
                'grade_point' => 3.75,
                'description' => 'Very strong performance with excellent understanding of concepts.',
                'feedback_template' => 'Very good work! You achieved {score}% showing excellent understanding.',
                'color_code' => '#66BB6A',
                'display_order' => 2,
            ],
            [
                'min_score' => 85.00,
                'max_score' => 89.99,
                'grade_label' => 'Good',
                'grade_code' => 'B+',
                'grade_point' => 3.50,
                'description' => 'Strong performance with good understanding of most concepts.',
                'feedback_template' => 'Good job! Your score of {score}% demonstrates strong understanding.',
                'color_code' => '#8BC34A',
                'display_order' => 3,
            ],
            [
                'min_score' => 80.00,
                'max_score' => 84.99,
                'grade_label' => 'Above Average',
                'grade_code' => 'B',
                'grade_point' => 3.00,
                'description' => 'Above average performance with solid understanding of key concepts.',
                'feedback_template' => 'Well done! You scored {score}% showing solid understanding.',
                'color_code' => '#CDDC39',
                'display_order' => 4,
            ],
            [
                'min_score' => 75.00,
                'max_score' => 79.99,
                'grade_label' => 'Satisfactory',
                'grade_code' => 'C+',
                'grade_point' => 2.50,
                'description' => 'Satisfactory performance demonstrating acceptable understanding.',
                'feedback_template' => 'Your score of {score}% shows satisfactory understanding. Keep up the effort!',
                'color_code' => '#FFEB3B',
                'display_order' => 5,
            ],
            [
                'min_score' => 70.00,
                'max_score' => 74.99,
                'grade_label' => 'Fair',
                'grade_code' => 'C',
                'grade_point' => 2.00,
                'description' => 'Fair performance with basic understanding of concepts.',
                'feedback_template' => 'You scored {score}%. Consider reviewing the material to strengthen your understanding.',
                'color_code' => '#FFC107',
                'display_order' => 6,
            ],
            [
                'min_score' => 60.00,
                'max_score' => 69.99,
                'grade_label' => 'Passing',
                'grade_code' => 'D',
                'grade_point' => 1.00,
                'description' => 'Minimal passing performance. Additional study recommended.',
                'feedback_template' => 'You passed with {score}%. Additional review of concepts is strongly recommended.',
                'color_code' => '#FF9800',
                'display_order' => 7,
            ],
            [
                'min_score' => 0.00,
                'max_score' => 59.99,
                'grade_label' => 'Needs Improvement',
                'grade_code' => 'F',
                'grade_point' => 0.00,
                'description' => 'Performance below passing standards. Significant improvement needed.',
                'feedback_template' => 'Your score of {score}% indicates areas that need significant improvement. Please seek additional help.',
                'color_code' => '#F44336',
                'display_order' => 8,
            ],
        ];

        foreach ($globalCriteria as $criteria) {
            AssessmentGradingCriteria::firstOrCreate(
                [
                    'category_id' => null,
                    'subcategory_id' => null,
                    'min_score' => $criteria['min_score'],
                    'max_score' => $criteria['max_score'],
                ],
                $criteria
            );
        }

        $this->command->info('✓ Global grading criteria seeded successfully.');

        // You can add category-specific or subcategory-specific criteria here
        // Example: Technical Skill specific criteria
        $technicalSkillCategory = \App\Models\Category::where('category_name', 'Technical Skill')->first();

        if ($technicalSkillCategory) {
            $technicalCriteria = [
                [
                    'category_id' => $technicalSkillCategory->id,
                    'min_score' => 90.00,
                    'max_score' => 100.00,
                    'grade_label' => 'Advanced',
                    'grade_code' => 'ADV',
                    'grade_point' => 4.00,
                    'description' => 'Advanced technical proficiency with ability to solve complex problems independently.',
                    'feedback_template' => 'Exceptional technical skills! You achieved {score}% in {subcategory}.',
                    'color_code' => '#2196F3',
                    'display_order' => 1,
                ],
                [
                    'category_id' => $technicalSkillCategory->id,
                    'min_score' => 75.00,
                    'max_score' => 89.99,
                    'grade_label' => 'Intermediate',
                    'grade_code' => 'INT',
                    'grade_point' => 3.00,
                    'description' => 'Intermediate technical skills with solid foundation.',
                    'feedback_template' => 'Good technical skills! You scored {score}% in {subcategory}.',
                    'color_code' => '#03A9F4',
                    'display_order' => 2,
                ],
                [
                    'category_id' => $technicalSkillCategory->id,
                    'min_score' => 60.00,
                    'max_score' => 74.99,
                    'grade_label' => 'Basic',
                    'grade_code' => 'BAS',
                    'grade_point' => 2.00,
                    'description' => 'Basic technical understanding with room for growth.',
                    'feedback_template' => 'You have basic skills with {score}% in {subcategory}. Continue practicing!',
                    'color_code' => '#00BCD4',
                    'display_order' => 3,
                ],
                [
                    'category_id' => $technicalSkillCategory->id,
                    'min_score' => 0.00,
                    'max_score' => 59.99,
                    'grade_label' => 'Developing',
                    'grade_code' => 'DEV',
                    'grade_point' => 1.00,
                    'description' => 'Technical skills are still developing. Additional training recommended.',
                    'feedback_template' => 'Your technical skills in {subcategory} are developing ({score}%). Additional practice recommended.',
                    'color_code' => '#FF5722',
                    'display_order' => 4,
                ],
            ];

            foreach ($technicalCriteria as $criteria) {
                AssessmentGradingCriteria::firstOrCreate(
                    [
                        'category_id' => $criteria['category_id'],
                        'subcategory_id' => $criteria['subcategory_id'] ?? null,
                        'min_score' => $criteria['min_score'],
                        'max_score' => $criteria['max_score'],
                    ],
                    $criteria
                );
            }

            $this->command->info('✓ Technical Skill category criteria seeded successfully.');
        }

        $this->command->info("\nAssessment Grading Criteria seeding completed!");
        $this->command->info("Total criteria created: " . AssessmentGradingCriteria::count());
    }
}

