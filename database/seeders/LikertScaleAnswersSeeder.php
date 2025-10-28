<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Database\Seeder;

class LikertScaleAnswersSeeder extends Seeder
{
    /**
     * Add Likert scale answers (1-5) to all Technical Skill and Soft Skill questions
     * that don't have answers yet.
     */
    public function run(): void
    {
        $this->command->info('Adding Likert scale answers to assessment questions...');

        $likertAnswers = [
            ['text' => '1 - Novice', 'order' => 1],
            ['text' => '2 - Beginner', 'order' => 2],
            ['text' => '3 - Intermediate', 'order' => 3],
            ['text' => '4 - Expert', 'order' => 4],
            ['text' => '5 - Advanced', 'order' => 5],
        ];

        // Get Technical Skill and Soft Skill categories
        $categories = Category::whereIn('category_name', ['Technical Skill', 'Soft Skill'])->get();

        $questionsUpdated = 0;
        $answersCreated = 0;

        foreach ($categories as $category) {
            $this->command->info("Processing category: {$category->category_name}");

            // Get all subcategories for this category
            $subcategories = SubCategory::where('category_id', $category->id)->get();

            foreach ($subcategories as $subcategory) {
                // Get all questions for this subcategory
                $questions = Question::where('subcategory_id', $subcategory->id)
                    ->where('is_active', true)
                    ->get();

                foreach ($questions as $question) {
                    // Check if question already has answers
                    $existingAnswersCount = Answer::where('question_id', $question->id)->count();

                    if ($existingAnswersCount === 0) {
                        // Add Likert scale answers
                        foreach ($likertAnswers as $answerData) {
                            Answer::create([
                                'question_id' => $question->id,
                                'answer_text' => $answerData['text'],
                                'is_correct' => false, // Not applicable for self-assessment
                                'display_order' => $answerData['order'],
                            ]);
                            $answersCreated++;
                        }
                        $questionsUpdated++;
                    }
                }
            }
        }

        $this->command->info("\nLikert Scale Answers Seeder completed!");
        $this->command->info("Questions updated: {$questionsUpdated}");
        $this->command->info("Answers created: {$answersCreated}");
    }
}

