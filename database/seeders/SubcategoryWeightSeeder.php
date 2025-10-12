<?php

namespace Database\Seeders;

use App\Models\HTE;
use App\Models\Internship;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\SubcategoryWeight;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubcategoryWeightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories and subcategories
        $categories = Category::with('subCategories')->get();
        
        // Get all internships
        $internships = Internship::all();

        // Create subcategory weights for specific internships (reduced to minimum)
        $subcategoryWeightData = [
            // TechCorp Solutions - Software Development Intern
            [
                'internship_id' => $internships->where('position_title', 'Software Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'Java')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Software Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'Python')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Software Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'JavaScript')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Software Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'System and Software Development')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Software Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'Web Development')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Software Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Problem-Solving and Analytical Skills')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Software Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Communication Skills')->first()->id,
                'weight' => 10,
            ],

            // TechCorp Solutions - Data Science Intern
            [
                'internship_id' => $internships->where('position_title', 'Data Science Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'Python')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Science Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'SQL')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Science Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'Database Management')->first()->id,
                'weight' => 25,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Science Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'System and Software Development')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Science Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Problem-Solving and Analytical Skills')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Science Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Communication Skills')->first()->id,
                'weight' => 10,
            ],

            // MariaTech Solutions - Full-Stack Development Intern
            [
                'internship_id' => $internships->where('position_title', 'Full-Stack Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'JavaScript')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Full-Stack Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'Python')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Full-Stack Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'Web Development')->first()->id,
                'weight' => 25,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Full-Stack Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'System and Software Development')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Full-Stack Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Problem-Solving and Analytical Skills')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Full-Stack Development Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Communication Skills')->first()->id,
                'weight' => 10,
            ],

            // MariaTech Solutions - UI/UX Design Intern
            [
                'internship_id' => $internships->where('position_title', 'UI/UX Design Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'HTML/CSS')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'UI/UX Design Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'JavaScript')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'UI/UX Design Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'Web Development')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'UI/UX Design Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Communication Skills')->first()->id,
                'weight' => 25,
            ],
            [
                'internship_id' => $internships->where('position_title', 'UI/UX Design Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Problem-Solving and Analytical Skills')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'UI/UX Design Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Adaptability and Learning')->first()->id,
                'weight' => 15,
            ],
        ];

        foreach ($subcategoryWeightData as $data) {
            SubcategoryWeight::firstOrCreate(
                [
                    'internship_id' => $data['internship_id'],
                    'subcategory_id' => $data['subcategory_id'],
                ],
                [
                    'internship_id' => $data['internship_id'],
                    'subcategory_id' => $data['subcategory_id'],
                    'weight' => $data['weight'],
                ]
            );
        }

        // Note: We only create weights for subcategories that have explicit criteria defined
        // This prevents creating unnecessary 0-weight entries that could cause conflicts
        // The InternshipCriteriaSeeder handles comprehensive criteria creation
    }
}
