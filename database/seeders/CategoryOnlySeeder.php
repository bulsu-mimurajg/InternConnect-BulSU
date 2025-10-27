<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class CategoryOnlySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Technical Skill' => [
                'Database Management' => [],
                'Web Development' => [],
                'System and Software Development' => [],
            ],
            'Soft Skill' => [
                'Communication Skills' => [],
                'Problem-Solving and Analytical Skills' => [],
                'Time Management' => [],
                'Adaptability and Learning' => [],
                'Ethical Decision-Making' => [],
                'Professionalism' => [],
            ],
        ];

        foreach ($categories as $categoryName => $subCategories) {
            // Create or retrieve the category
            $category = Category::firstOrCreate(['category_name' => $categoryName]);

            foreach ($subCategories as $subCategoryName => $questions) {
                // Create or retrieve the subcategory (no questions created here)
                SubCategory::firstOrCreate([
                    'subcategory_name' => $subCategoryName,
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
