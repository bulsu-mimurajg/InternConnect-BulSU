<?php

namespace Database\Seeders;

use App\Models\HTE;
use App\Models\Internship;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\SubcategoryWeight;
use Illuminate\Database\Seeder;

class InternshipCriteriaSeeder extends Seeder
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

        // Define comprehensive criteria for each internship type using only existing subcategories
        $internshipCriteria = [
            'Software Development Intern' => [
                'Language Proficiency' => [
                    'Java' => 20,
                    'Python' => 15,
                    'JavaScript' => 15,
                    'C++' => 10,
                    'SQL' => 5,
                    'HTML/CSS' => 5,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 25,
                    'Web Development' => 20,
                    'Database Management' => 10,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 20,
                    'Communication Skills' => 15,
                    'Time Management' => 10,
                ]
            ],
            
            'Data Analytics Intern' => [
                'Language Proficiency' => [
                    'Python' => 25,
                    'SQL' => 20,
                    'JavaScript' => 5,
                    'Java' => 5,
                ],
                'Technical Skill' => [
                    'Database Management' => 30,
                    'System and Software Development' => 20,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 25,
                    'Communication Skills' => 15,
                    'Adaptability and Learning' => 10,
                ]
            ],
            
            'Environmental Research Intern' => [
                'Language Proficiency' => [
                    'Python' => 20,
                    'SQL' => 15,
                    'JavaScript' => 5,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 25,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 25,
                    'Problem-Solving and Analytical Skills' => 20,
                    'Adaptability and Learning' => 15,
                ]
            ],
            
            'Digital Marketing Intern' => [
                'Language Proficiency' => [
                    'HTML/CSS' => 15,
                    'JavaScript' => 15,
                    'Python' => 10,
                    'SQL' => 5,
                ],
                'Technical Skill' => [
                    'Web Development' => 25,
                    'System and Software Development' => 15,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 25,
                    'Problem-Solving and Analytical Skills' => 20,
                    'Time Management' => 10,
                    'Adaptability and Learning' => 10,
                ]
            ],
            
            'Finance Intern' => [
                'Language Proficiency' => [
                    'SQL' => 25,
                    'Python' => 20,
                    'Java' => 5,
                ],
                'Technical Skill' => [
                    'Database Management' => 25,
                    'System and Software Development' => 15,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 20,
                    'Problem-Solving and Analytical Skills' => 20,
                    'Professionalism' => 15,
                    'Time Management' => 10,
                ]
            ],
            
            'Cybersecurity Intern' => [
                'Language Proficiency' => [
                    'Python' => 25,
                    'JavaScript' => 15,
                    'C++' => 15,
                    'SQL' => 10,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 30,
                    'Web Development' => 15,
                    'Database Management' => 10,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 25,
                    'Communication Skills' => 15,
                    'Ethical Decision-Making' => 15,
                    'Adaptability and Learning' => 10,
                ]
            ],
            
            'UX/UI Design Intern' => [
                'Language Proficiency' => [
                    'HTML/CSS' => 20,
                    'JavaScript' => 15,
                    'Python' => 10,
                    'SQL' => 5,
                ],
                'Technical Skill' => [
                    'Web Development' => 25,
                    'System and Software Development' => 15,
                ],
                'Soft Skill' => [
                    'Communication Skills' => 25,
                    'Problem-Solving and Analytical Skills' => 15,
                    'Time Management' => 10,
                ]
            ],
            
            'AI/ML Intern' => [
                'Language Proficiency' => [
                    'Python' => 30,
                    'SQL' => 15,
                    'C++' => 10,
                ],
                'Technical Skill' => [
                    'System and Software Development' => 25,
                    'Database Management' => 20,
                ],
                'Soft Skill' => [
                    'Problem-Solving and Analytical Skills' => 25,
                    'Communication Skills' => 15,
                    'Adaptability and Learning' => 15,
                ]
            ]
        ];

        // Create criteria for each internship
        foreach ($internshipCriteria as $positionTitle => $categoryCriteria) {
            $internship = $internships->where('position_title', $positionTitle)->first();
            
            if (!$internship) {
                continue; // Skip if internship doesn't exist
            }

            foreach ($categoryCriteria as $categoryName => $subcategoryWeights) {
                $category = $categories->where('category_name', $categoryName)->first();
                
                if (!$category) {
                    continue;
                }

                foreach ($subcategoryWeights as $subcategoryName => $weight) {
                    $subcategory = $category->subCategories->where('subcategory_name', $subcategoryName)->first();
                    
                    if (!$subcategory) {
                        continue;
                    }

                    // Create or update the subcategory weight
                    SubcategoryWeight::updateOrCreate(
                        [
                            'internship_id' => $internship->id,
                            'subcategory_id' => $subcategory->id,
                        ],
                        [
                            'weight' => $weight,
                        ]
                    );
                }
            }
        }

        // Set remaining subcategories to 0 weight for completeness
        foreach ($internships as $internship) {
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

        $this->command->info('Internship criteria seeded successfully!');
    }
}
