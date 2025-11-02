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

class AssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_submission_stores_mean_scores()
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
        
        // Create a section first
        $section = \App\Models\Section::create([
            'section_name' => 'TEST-SECTION',
            'status' => 'active'
        ]);

        // Create a user and student
        $user = User::factory()->create();
        $user->assignRole('student');
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

        // Create a category and subcategory
        $category = Category::create([
            'category_name' => 'Technical Skill',
        ]);

        $subcategory = SubCategory::create([
            'category_id' => $category->id,
            'subcategory_name' => 'Programming',
        ]);

        // Create questions for the subcategory (quiz type)
        $question1 = Question::create([
            'subcategory_id' => $subcategory->id,
            'question' => 'How proficient are you in PHP?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        $question2 = Question::create([
            'subcategory_id' => $subcategory->id,
            'question' => 'How proficient are you in JavaScript?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        // Create choices for question 1
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

        // Create choices for question 2
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

        // Prepare assessment data - use category name for field names
        $fieldName1 = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $category->category_name)) . '_' . $question1->id;
        $fieldName2 = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $category->category_name)) . '_' . $question2->id;

        // Submit one correct (score 5) and one incorrect (score 1) = mean 3
        $assessmentData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'middleName' => 'M',
            $fieldName1 => $choice1Correct->id, // Correct = score 5
            $fieldName2 => $choice2Incorrect->id, // Incorrect = score 1
        ];

        // Submit assessment
        $response = $this->actingAs($user)->post('/assessment', $assessmentData);

        // Assert response
        $response->assertRedirect('/assessment');
        $response->assertSessionHas('success');

        // Check that mean score was stored
        $studentScore = StudentScore::where('student_id', $student->id)
            ->where('sub_category_id', $subcategory->id)
            ->first();

        $this->assertNotNull($studentScore);
        $this->assertEquals(3.0, $studentScore->score); // Mean of 5 (correct) and 1 (incorrect) is 3.0
    }

    public function test_student_is_submit_field_is_updated_after_assessment_submission()
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
        
        // Create a section first
        $section = \App\Models\Section::create([
            'section_name' => 'TEST-SECTION-2',
            'status' => 'active'
        ]);

        // Create a user and student
        $user = User::factory()->create();
        $user->assignRole('student');
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
            'is_submit' => false
        ]);

        // Create categories and subcategories
        $category = Category::create([
            'category_name' => 'Technical Skill',
        ]);

        $subcategory = SubCategory::create([
            'category_id' => $category->id,
            'subcategory_name' => 'Programming',
        ]);

        // Create question (quiz type)
        $question = Question::create([
            'subcategory_id' => $subcategory->id,
            'question' => 'How proficient are you in programming?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        // Create choices for the question
        $choiceCorrect = \App\Models\Choice::create([
            'question_id' => $question->id,
            'choice_text' => 'Very Proficient',
            'is_correct' => true,
        ]);

        // Prepare assessment data - use category name for field names
        $fieldName = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $category->category_name)) . '_' . $question->id;

        $assessmentData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'middleName' => 'M',
            'suffix' => '',
            'province' => 'Bulacan',
            'city' => 'Malolos',
            'zip' => '3000',
            $fieldName => $choiceCorrect->id
        ];

        // Submit assessment
        $response = $this->actingAs($user)->post('/assessment', $assessmentData);

        // Assert the student's is_submit field is updated to true
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'is_submit' => true
        ]);

        $response->assertRedirect('/assessment');
        $response->assertSessionHas('success');
    }

    public function test_assessment_form_shows_success_state_after_submission()
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
        
        // Create a section first
        $section = \App\Models\Section::create([
            'section_name' => 'TEST-SECTION-3',
            'status' => 'active'
        ]);
        
        // Create a user and student
        $user = User::factory()->create();
        $user->assignRole('student');
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
            'is_submit' => false
        ]);

        // Create categories and subcategories
        $category = Category::create([
            'category_name' => 'Technical Skill',
        ]);

        $subcategory = SubCategory::create([
            'category_id' => $category->id,
            'subcategory_name' => 'Programming',
        ]);

        // Create question (quiz type)
        $question = Question::create([
            'subcategory_id' => $subcategory->id,
            'question' => 'How proficient are you in programming?',
            'question_type' => 'quiz',
            'is_active' => true,
        ]);

        // Create choices for the question
        $choiceCorrect = \App\Models\Choice::create([
            'question_id' => $question->id,
            'choice_text' => 'Very Proficient',
            'is_correct' => true,
        ]);

        // Prepare assessment data - use category name for field names
        $fieldName = strtolower(str_replace(['+', '/', ' ', '-'], ['_', '_', '_', '_'], $category->category_name)) . '_' . $question->id;

        $assessmentData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'middleName' => 'M',
            'suffix' => '',
            'province' => 'Bulacan',
            'city' => 'Malolos',
            'zip' => '3000',
            $fieldName => $choiceCorrect->id
        ];

        // Submit assessment
        $response = $this->actingAs($user)->post('/assessment', $assessmentData);

        // Assert the student's is_submit field is updated to true
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'is_submit' => true
        ]);

        $response->assertRedirect('/assessment');
        $response->assertSessionHas('success');

        // Visit the assessment page again to see the success state
        $response = $this->actingAs($user)->get('/assessment');
        $response->assertStatus(200);
        // The success state should be handled by the frontend component
    }

    public function test_assessment_form_shows_success_state_for_already_submitted_student()
    {
        // Seed roles and permissions
        $this->seed(RolePermissionSeeder::class);
        
        // Create a section first
        $section = \App\Models\Section::create([
            'section_name' => 'TEST-SECTION-4',
            'status' => 'active'
        ]);

        // Create a user and student who has already submitted
        $user = User::factory()->create();
        $user->assignRole('student');
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
            'is_submit' => true // Already submitted
        ]);

        // Visit the assessment page
        $response = $this->actingAs($user)->get('/assessment');
        
        // Should return 200 and the page should show success state
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('student/assessment')
                ->where('hasSubmitted', 1)
        );
    }
} 