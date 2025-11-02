<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Question;
use App\Models\StudentScore;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssessmentValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_requires_all_fields_to_be_filled()
    {
        // Seed roles and permissions
        $this->seed(RolePermissionSeeder::class);
        
        // Create an active internship season
        $season = \App\Models\InternshipSeason::create([
            'name' => 'Test Season 2024',
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(30),
            'status' => 'active'
        ]);
        
        // Create an active deadline for student assessment form
        \App\Models\Deadline::create([
            'title' => 'Student Assessment Form Deadline',
            'category' => 'student_assessment_form',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'status' => 'active',
            'internship_season_id' => $season->id
        ]);
        
        // Create a user and student
        $user = User::factory()->create();
        $user->assignRole('student');
        // Create a section first
        $section = \App\Models\Section::create([
            'section_name' => 'TEST-SECTION-VAL',
            'status' => 'active'
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => 'STU' . $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'M',
            'phone' => '',
            'section_id' => $section->section_id,
            'specialization' => '',
            'address' => '',
            'birth_date' => now(),
        ]);

        // Create categories and subcategories
        $technicalCategory = Category::create(['category_name' => 'Technical Skill']);
        $languageCategory = Category::create(['category_name' => 'Language Proficiency']);
        $softCategory = Category::create(['category_name' => 'Soft Skill']);

        $programmingSubcategory = SubCategory::create([
            'category_id' => $technicalCategory->id,
            'subcategory_name' => 'Programming',
        ]);

        $javaSubcategory = SubCategory::create([
            'category_id' => $languageCategory->id,
            'subcategory_name' => 'Java',
        ]);

        $communicationSubcategory = SubCategory::create([
            'category_id' => $softCategory->id,
            'subcategory_name' => 'Communication',
        ]);

        // Create questions (quiz type)
        $question1 = Question::create([
            'subcategory_id' => $programmingSubcategory->id,
            'question' => 'How proficient are you in PHP?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        $question2 = Question::create([
            'subcategory_id' => $javaSubcategory->id,
            'question' => 'How proficient are you in Java?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        $question3 = Question::create([
            'subcategory_id' => $communicationSubcategory->id,
            'question' => 'How well do you communicate?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        // Create choices for each question
        $choice1Correct = \App\Models\Choice::create([
            'question_id' => $question1->id,
            'choice_text' => 'Very Proficient',
            'is_correct' => true,
        ]);
        $choice1Incorrect = \App\Models\Choice::create([
            'question_id' => $question1->id,
            'choice_text' => 'Not Proficient',
            'is_correct' => false,
        ]);

        $choice2Correct = \App\Models\Choice::create([
            'question_id' => $question2->id,
            'choice_text' => 'Very Proficient',
            'is_correct' => true,
        ]);
        $choice2Incorrect = \App\Models\Choice::create([
            'question_id' => $question2->id,
            'choice_text' => 'Not Proficient',
            'is_correct' => false,
        ]);

        $choice3Correct = \App\Models\Choice::create([
            'question_id' => $question3->id,
            'choice_text' => 'Very Well',
            'is_correct' => true,
        ]);
        $choice3Incorrect = \App\Models\Choice::create([
            'question_id' => $question3->id,
            'choice_text' => 'Not Well',
            'is_correct' => false,
        ]);

        // Test 1: Submission with missing fields should fail
        $incompleteData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            // Missing question fields
        ];

        $response = $this->actingAs($user)->post('/assessment', $incompleteData);
        $response->assertSessionHasErrors(); // Should have validation errors for missing question fields

        // Test 2: Submission with all fields should succeed and store mean scores
        // Use category name for field names
        $fieldName1 = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $technicalCategory->category_name)) . '_' . $question1->id;
        $fieldName2 = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $languageCategory->category_name)) . '_' . $question2->id;
        $fieldName3 = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $softCategory->category_name)) . '_' . $question3->id;

        $completeData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'middleName' => 'M',
            $fieldName1 => $choice1Correct->id, // PHP: correct = score 5
            $fieldName2 => $choice2Correct->id, // Java: correct = score 5
            $fieldName3 => $choice3Incorrect->id, // Communication: incorrect = score 1
        ];

        $response = $this->actingAs($user)->post('/assessment', $completeData);
        $response->assertRedirect('/assessment');
        $response->assertSessionHas('success');

        // Verify mean scores were stored correctly
        $programmingScore = StudentScore::where('student_id', $student->id)
            ->where('sub_category_id', $programmingSubcategory->id)
            ->first();
        $this->assertNotNull($programmingScore);
        $this->assertEquals(5.0, $programmingScore->score); // Correct answer = score 5

        $javaScore = StudentScore::where('student_id', $student->id)
            ->where('sub_category_id', $javaSubcategory->id)
            ->first();
        $this->assertNotNull($javaScore);
        $this->assertEquals(5.0, $javaScore->score); // Correct answer = score 5

        $communicationScore = StudentScore::where('student_id', $student->id)
            ->where('sub_category_id', $communicationSubcategory->id)
            ->first();
        $this->assertNotNull($communicationScore);
        $this->assertEquals(1.0, $communicationScore->score); // Incorrect answer = score 1
    }

    public function test_assessment_computes_correct_mean_scores_for_multiple_questions()
    {
        // Seed roles and permissions
        $this->seed(RolePermissionSeeder::class);
        
        // Create an active internship season
        $season = \App\Models\InternshipSeason::create([
            'name' => 'Test Season 2024',
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(30),
            'status' => 'active'
        ]);
        
        // Create an active deadline for student assessment form
        \App\Models\Deadline::create([
            'title' => 'Student Assessment Form Deadline',
            'category' => 'student_assessment_form',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'status' => 'active',
            'internship_season_id' => $season->id
        ]);
        
        // Create a user and student
        $user = User::factory()->create();
        $user->assignRole('student');
        // Create a section first
        $section = \App\Models\Section::create([
            'section_name' => 'TEST-SECTION-VAL-2',
            'status' => 'active'
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => 'STU' . $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'middle_name' => 'A',
            'phone' => '',
            'section_id' => $section->section_id,
            'specialization' => '',
            'address' => '',
            'birth_date' => now(),
        ]);

        // Create a category and subcategory with multiple questions
        $technicalCategory = Category::create(['category_name' => 'Technical Skill']);
        $programmingSubcategory = SubCategory::create([
            'category_id' => $technicalCategory->id,
            'subcategory_name' => 'Programming',
        ]);

        // Create multiple questions for the same subcategory (quiz type)
        $question1 = Question::create([
            'subcategory_id' => $programmingSubcategory->id,
            'question' => 'How proficient are you in PHP?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        $question2 = Question::create([
            'subcategory_id' => $programmingSubcategory->id,
            'question' => 'How proficient are you in JavaScript?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        $question3 = Question::create([
            'subcategory_id' => $programmingSubcategory->id,
            'question' => 'How proficient are you in Python?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        // Create choices for each question
        // Question 1: correct choice
        $choice1Correct = \App\Models\Choice::create([
            'question_id' => $question1->id,
            'choice_text' => 'Very Proficient',
            'is_correct' => true,
        ]);
        $choice1Incorrect = \App\Models\Choice::create([
            'question_id' => $question1->id,
            'choice_text' => 'Not Proficient',
            'is_correct' => false,
        ]);

        // Question 2: correct choice
        $choice2Correct = \App\Models\Choice::create([
            'question_id' => $question2->id,
            'choice_text' => 'Very Proficient',
            'is_correct' => true,
        ]);
        $choice2Incorrect = \App\Models\Choice::create([
            'question_id' => $question2->id,
            'choice_text' => 'Not Proficient',
            'is_correct' => false,
        ]);

        // Question 3: incorrect choice
        $choice3Correct = \App\Models\Choice::create([
            'question_id' => $question3->id,
            'choice_text' => 'Very Proficient',
            'is_correct' => true,
        ]);
        $choice3Incorrect = \App\Models\Choice::create([
            'question_id' => $question3->id,
            'choice_text' => 'Not Proficient',
            'is_correct' => false,
        ]);

        // Submit assessment - use category name for field names
        $fieldName1 = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $technicalCategory->category_name)) . '_' . $question1->id;
        $fieldName2 = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $technicalCategory->category_name)) . '_' . $question2->id;
        $fieldName3 = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $technicalCategory->category_name)) . '_' . $question3->id;

        $assessmentData = [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'middleName' => 'A',
            $fieldName1 => $choice1Correct->id, // PHP: correct = 5
            $fieldName2 => $choice2Correct->id, // JavaScript: correct = 5
            $fieldName3 => $choice3Incorrect->id, // Python: incorrect = 1
        ];

        $response = $this->actingAs($user)->post('/assessment', $assessmentData);
        $response->assertRedirect('/assessment');
        $response->assertSessionHas('success');

        // Verify mean score calculation: (5 + 5 + 1) / 3 = 3.67
        $programmingScore = StudentScore::where('student_id', $student->id)
            ->where('sub_category_id', $programmingSubcategory->id)
            ->first();
        
        $this->assertNotNull($programmingScore);
        $this->assertEqualsWithDelta(3.67, $programmingScore->score, 0.01); // Mean of 5, 5, 1 = 3.67
    }
} 