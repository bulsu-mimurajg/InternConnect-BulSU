<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Question;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Technical Skill' => [
                'Database Management' => [
                    'Designing Databases',
                    'Writing SQL Queries',
                    'Database Administration',
                    'Using tools like MySQL, Oracle etc.'
                ],
                'Web Development' => [
                    'Designing user interfaces (UI)',
                    'Developing responsive websites',
                    'Using front-end frameworks (e.g., Bootstrap, React)',
                    'Back-end development (e.g., Node.js, Django)'
                ],
                'System and Software Development' => [
                    'Gathering and analyzing requirements',
                    'Software design and architecture',
                    'Development using Agile/Scrum',
                    'Testing and debugging applications',
                    'System maintenance and troubleshooting'
                ],
            ],
            'Soft Skill' => [
                'Communication Skills' => [
                    'Explaining technical concepts to non-technical people',
                    'Collaborating with team members',
                    'Writing clear documentation and reports'
                ],
                'Problem-Solving and Analytical Skills' => [
                    'Independently solve complex problems or debug issues?',
                    'Research solutions before seeking help from others?',
                    'Think critically when troubleshooting technical problems?'
                ],
                'Time Management' => [
                    'Prioritizing tasks effectively',
                    'Meeting project deadlines'
                ],
                'Adaptability and Learning' => [
                    'Adapt to new tools and technologies quickly?',
                    'Show a willingness to learn independently?',
                    'Stay updated on emerging IT trends?'
                ],
                'Ethical Decision-Making' => [
                    'Data privacy and security protocols?',
                    'Ethical issues like intellectual property rights?'
                ],
                'Professionalism' => [
                    'Punctuality and reliability',
                    'Following company policies and procedures',
                    'Being receptive to constructive feedback and improving performance accordingly'
                ],
            ],
        ];

        foreach ($categories as $categoryName => $subCategories) {
            // Create or retrieve the category with type
            $categoryType = $categoryName === 'Technical Skill' ? 'technical' : 'soft_skills';
            $category = Category::firstOrCreate(
                ['category_name' => $categoryName],
                ['category_type' => $categoryType]
            );
            
            // Update existing categories with type if not set
            if (!$category->category_type) {
                $category->update(['category_type' => $categoryType]);
            }

            foreach ($subCategories as $subCategoryName => $questions) {
                // Create or retrieve the subcategory
                $subCategory = SubCategory::firstOrCreate([
                    'subcategory_name' => $subCategoryName,
                    'category_id' => $category->id,
                ]);

                foreach ($questions as $questionText) {
                    // Create question aligned with new table
                    Question::firstOrCreate([
                        'question' => $questionText,
                        'subcategory_id' => $subCategory->id,
                        'is_active' => true
                    ]);
                }
            }
        }
    }
}
