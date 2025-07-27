<?php

namespace Database\Seeders;

use App\Models\SubCategory;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        SubCategory::create(['Programming and Coding Skill'])->each(function ($subCategory) {
//            $subCategory->questions()->saveMany(QuestionFactory::times(3)->create());
//        });
//        SubCategory::create(['Communication Skill'])->each(function ($subCategory) {
//            $subCategory->questions()->saveMany(QuestionFactory::times(3)->create());
//        });
    }
}
