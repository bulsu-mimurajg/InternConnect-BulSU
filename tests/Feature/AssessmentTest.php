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
            'section' => '',
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

        // Create questions for the subcategory
        $question1 = Question::create([
            'subcategory_id' => $subcategory->id,
            'question' => 'How proficient are you in PHP?',
            'access' => 'Student',
            'is_active' => true,
        ]);

        $question2 = Question::create([
            'subcategory_id' => $subcategory->id,
            'question' => 'How proficient are you in JavaScript?',
            'access' => 'Student',
            'is_active' => true,
        ]);

        // Prepare assessment data
        $fieldName1 = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subcategory->subcategory_name)) . '_' . $question1->id;
        $fieldName2 = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subcategory->subcategory_name)) . '_' . $question2->id;

        $assessmentData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'middleName' => 'M',
            $fieldName1 => 4, // Score for question 1
            $fieldName2 => 5, // Score for question 2
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
        $this->assertEquals(4.5, $studentScore->score); // Mean of 4 and 5 is 4.5
    }

    public function test_student_is_submit_field_is_updated_after_assessment_submission()
    {
        // Seed roles and permissions
        $this->seed(RolePermissionSeeder::class);
        
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
            'section' => '',
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

        // Create questions
        $question = Question::create([
            'subcategory_id' => $subcategory->id,
            'question' => 'How proficient are you in programming?',
            'access' => 'Student',
            'is_active' => true,
        ]);

        // Prepare assessment data
        $fieldName = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subcategory->subcategory_name)) . '_' . $question->id;

        $assessmentData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'middleName' => 'M',
            'suffix' => '',
            'province' => 'Bulacan',
            'city' => 'Malolos',
            'zip' => '3000',
            $fieldName => 4
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
            'section' => '',
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

        // Create questions
        $question = Question::create([
            'subcategory_id' => $subcategory->id,
            'question' => 'How proficient are you in programming?',
            'access' => 'Student',
            'is_active' => true,
        ]);

        // Prepare assessment data
        $fieldName = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subcategory->subcategory_name)) . '_' . $question->id;

        $assessmentData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'middleName' => 'M',
            'suffix' => '',
            'province' => 'Bulacan',
            'city' => 'Malolos',
            'zip' => '3000',
            $fieldName => 4
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
            'section' => '',
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