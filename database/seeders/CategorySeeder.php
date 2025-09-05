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
            'Language Proficiency' => [
                'Java' => [
                    'What is your proficiency level in Java?',
                    'Have you worked with Java frameworks such as Spring?',
                    'Can you write Java programs following OOP principles?'
                ],
                'C++' => [
                    'Are you comfortable with C++ memory management?',
                    'Have you used STL in C++ programming?',
                    'Can you develop applications using C++ classes and objects?'
                ],
                'Python' => [
                    'Do you have experience with Python scripting?',
                    'Have you worked with Python frameworks like Django or Flask?',
                    'Can you automate tasks using Python?'
                ],
                'HTML/CSS' => [
                    'Are you proficient in writing semantic HTML?',
                    'Can you style websites effectively using CSS?',
                    'Have you worked with CSS preprocessors like SASS or LESS?'
                ],
                'JavaScript' => [
                    'Do you have experience with vanilla JavaScript?',
                    'Have you used any JS frameworks like React or Angular?',
                    'Can you manipulate the DOM with JavaScript?'
                ],
                'PHP' => [
                    'Are you familiar with PHP syntax and features?',
                    'Have you developed web applications using PHP?',
                    'Can you work with PHP frameworks such as Laravel or CodeIgniter?'
                ],
                'SQL' => [
                    'Can you write complex SQL queries?',
                    'Have you worked with database normalization?',
                    'Do you know how to optimize SQL queries for performance?'
                ],
            ],
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
            // Create or retrieve the category
            $category = Category::firstOrCreate(['category_name' => $categoryName]);

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
                        'access' => 'Student', // Default access
                        'is_active' => true
                    ]);
                }
            }
        }
    }
}
