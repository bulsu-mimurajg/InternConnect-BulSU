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

        // Create subcategory weights for specific internships
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

            // DataFlow Analytics - Data Analytics Intern
            [
                'internship_id' => $internships->where('position_title', 'Data Analytics Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'Python')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Analytics Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'SQL')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Analytics Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'Database Management')->first()->id,
                'weight' => 25,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Analytics Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'System and Software Development')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Analytics Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Problem-Solving and Analytical Skills')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Data Analytics Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Communication Skills')->first()->id,
                'weight' => 10,
            ],

            // GreenTech Industries - Environmental Research Intern
            [
                'internship_id' => $internships->where('position_title', 'Environmental Research Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'Python')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Environmental Research Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'SQL')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Environmental Research Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'System and Software Development')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Environmental Research Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Communication Skills')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Environmental Research Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Problem-Solving and Analytical Skills')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Environmental Research Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Adaptability and Learning')->first()->id,
                'weight' => 10,
            ],

            // Creative Marketing Pro - Digital Marketing Intern
            [
                'internship_id' => $internships->where('position_title', 'Digital Marketing Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'HTML/CSS')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Digital Marketing Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'JavaScript')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Digital Marketing Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'Web Development')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Digital Marketing Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'System and Software Development')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Digital Marketing Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Communication Skills')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Digital Marketing Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Problem-Solving and Analytical Skills')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Digital Marketing Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Time Management')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Digital Marketing Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Adaptability and Learning')->first()->id,
                'weight' => 10,
            ],

            // FinanceFirst Bank - Finance Intern
            [
                'internship_id' => $internships->where('position_title', 'Finance Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'SQL')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Finance Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Language Proficiency')->first()->subCategories->where('subcategory_name', 'Python')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Finance Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'Database Management')->first()->id,
                'weight' => 20,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Finance Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Technical Skill')->first()->subCategories->where('subcategory_name', 'System and Software Development')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Finance Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Communication Skills')->first()->id,
                'weight' => 15,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Finance Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Problem-Solving and Analytical Skills')->first()->id,
                'weight' => 10,
            ],
            [
                'internship_id' => $internships->where('position_title', 'Finance Intern')->first()->id,
                'subcategory_id' => $categories->where('category_name', 'Soft Skill')->first()->subCategories->where('subcategory_name', 'Professionalism')->first()->id,
                'weight' => 10,
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

        // Now ensure ALL subcategories have weights for internships that have entered criteria
        // Set remaining subcategories to 0 weight for completeness
        $internshipsWithCriteria = $internships->whereNotIn('position_title', ['Finance Intern']); // Exclude the one without criteria
        
        foreach ($internshipsWithCriteria as $internship) {
            foreach ($categories as $category) {
                foreach ($category->subCategories as $subcategory) {
                    // Check if this subcategory weight already exists
                    $existingWeight = SubcategoryWeight::where('internship_id', $internship->id)
                        ->where('subcategory_id', $subcategory->id)
                        ->first();
                    
                    if (!$existingWeight) {
                        // Create weight of 0 for unset subcategories
                        SubcategoryWeight::create([
                            'internship_id' => $internship->id,
                            'subcategory_id' => $subcategory->id,
                            'weight' => 0,
                        ]);
                    }
                }
            }
        }
    }
}
