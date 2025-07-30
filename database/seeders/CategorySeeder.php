<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Question;
use App\Models\SubCategory;
use Database\Factories\CategoryFactory;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        $personalCategory = [
//            'Personal Information' => [
//                'Basic Student Information' => 3,
//                'Address' => 3,
//            ]
//        ];
//
//        Category::factory()->create(['category_name' => 'Personal Information'])->each(function ($category) {
//            $subCategory = SubCategory::firstOrCreate(['subcategory_name' => 'Basic Student Information', 'category_id' => $category->id]);
//            $category->subCategory()->save($subCategory);
//            $subCategory->question()->saveMany(Question::factory()->times(3)->create(['sub_category_id' => $subCategory->id]));
//        });

        $categories = [
            'Personal Information' => [
                'Basic Student Information' => 4,
                'Address' => 3,
            ],
            'Technical Skill' => [
                'Programming and Coding Skill' => 3
            ],
            'Soft Skill' => [
                'Communication Skill' => 3
            ]
        ];

        foreach ($categories as $categoryName => $subCategories) {
            $category = Category::firstOrCreate(['category_name' => $categoryName]);

            foreach ($subCategories as $subCategoryName => $questionsCount) {
                $subCategory = SubCategory::firstOrCreate(
                    ['subcategory_name' => $subCategoryName, 'category_id' => $category->id],
                );

                $subCategory->questions()->saveMany(Question::factory()->times($questionsCount)->create(['sub_category_id' => $subCategory->id, 'access' => 'student']));
            }
        }

//        Category::factory()->create(['category_name' => 'Technical Skill'])->each(function ($category) {
//            $subCategory = SubCategory::firstOrCreate(['subcategory_name' => 'Programming and Coding Skill', 'category_id' => $category->id]);
//            $category->subCategory()->save($subCategory);
//            $subCategory->question()->saveMany(Question::factory()->times(3)->create(['sub_category_id' => $subCategory->id]));
//        });
//
//        Category::factory()->create(['category_name' => 'Soft Skill'])->each(function ($category) {
//            $subCategory = SubCategory::firstOrCreate(['subcategory_name' => 'Communication Skill', 'category_id' => $category->id]);
//            $category->subCategory()->save($subCategory);
//            $subCategory->question()->saveMany(Question::factory()->times(3)->create(['sub_category_id' => $subCategory->id]));
//        });

//        CategoryFactory::create(['Technical Skill'])->each(function ($category) {
//            $category->subCategory()->save(SubCategory::factory()->create(
//                'Programming and Coding Skill'
//            ))->each(function ($subCategory) {
//                $subCategory->questions()->saveMany(QuestionFactory::times(3)->create());
//            });
//        });
//        CategoryFactory::create(['Soft Skill'])->each(function ($category) {
//            $category->subCategory()->save(SubCategory::factory()->create(
//                'Communication Skill'
//            ))->each(function ($subCategory) {
//                $subCategory->questions()->saveMany(QuestionFactory::times(3)->create());
//            });
//        });

//        $technicalCategory = Category::factory()->create(['category_name' => 'Technical Skill']);
//        $technicalSubCategory = SubCategory::factory()->create(['subcategory_name' => 'Programming and Coding Skill', 'category_id' => $technicalCategory->id]);
//
//        $technicalSubCategory->questions()->saveMany(Question::factory()->count(3)->make());
    }
}
