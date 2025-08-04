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

        // Create questions
        $question1 = Question::create([
            'subcategory_id' => $programmingSubcategory->id,
            'question' => 'How proficient are you in PHP?',
            'access' => 'Student',
            'is_active' => true,
        ]);

        $question2 = Question::create([
            'subcategory_id' => $javaSubcategory->id,
            'question' => 'How proficient are you in Java?',
            'access' => 'Student',
            'is_active' => true,
        ]);

        $question3 = Question::create([
            'subcategory_id' => $communicationSubcategory->id,
            'question' => 'How well do you communicate?',
            'access' => 'Student',
            'is_active' => true,
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
        $fieldName1 = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $programmingSubcategory->subcategory_name)) . '_' . $question1->id;
        $fieldName2 = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $javaSubcategory->subcategory_name)) . '_' . $question2->id;
        $fieldName3 = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $communicationSubcategory->subcategory_name)) . '_' . $question3->id;

        $completeData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'middleName' => 'M',
            $fieldName1 => 4, // PHP proficiency
            $fieldName2 => 5, // Java proficiency  
            $fieldName3 => 3, // Communication
        ];

        $response = $this->actingAs($user)->post('/assessment', $completeData);
        $response->assertRedirect('/assessment');
        $response->assertSessionHas('success');

        // Verify mean scores were stored correctly
        $programmingScore = StudentScore::where('student_id', $student->id)
            ->where('sub_category_id', $programmingSubcategory->id)
            ->first();
        $this->assertNotNull($programmingScore);
        $this->assertEquals(4, $programmingScore->score); // Mean of 4 = 4

        $javaScore = StudentScore::where('student_id', $student->id)
            ->where('sub_category_id', $javaSubcategory->id)
            ->first();
        $this->assertNotNull($javaScore);
        $this->assertEquals(5, $javaScore->score); // Mean of 5 = 5

        $communicationScore = StudentScore::where('student_id', $student->id)
            ->where('sub_category_id', $communicationSubcategory->id)
            ->first();
        $this->assertNotNull($communicationScore);
        $this->assertEquals(3, $communicationScore->score); // Mean of 3 = 3
    }

    public function test_assessment_computes_correct_mean_scores_for_multiple_questions()
    {
        // Seed roles and permissions
        $this->seed(RolePermissionSeeder::class);
        
        // Create a user and student
        $user = User::factory()->create();
        $user->assignRole('student');
        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => 'STU' . $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'middle_name' => 'A',
            'phone' => '',
            'section' => '',
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

        // Create multiple questions for the same subcategory
        $question1 = Question::create([
            'subcategory_id' => $programmingSubcategory->id,
            'question' => 'How proficient are you in PHP?',
            'access' => 'Student',
            'is_active' => true,
        ]);

        $question2 = Question::create([
            'subcategory_id' => $programmingSubcategory->id,
            'question' => 'How proficient are you in JavaScript?',
            'access' => 'Student',
            'is_active' => true,
        ]);

        $question3 = Question::create([
            'subcategory_id' => $programmingSubcategory->id,
            'question' => 'How proficient are you in Python?',
            'access' => 'Student',
            'is_active' => true,
        ]);

        // Submit assessment with different scores
        $fieldName1 = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $programmingSubcategory->subcategory_name)) . '_' . $question1->id;
        $fieldName2 = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $programmingSubcategory->subcategory_name)) . '_' . $question2->id;
        $fieldName3 = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $programmingSubcategory->subcategory_name)) . '_' . $question3->id;

        $assessmentData = [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'middleName' => 'A',
            $fieldName1 => 3, // PHP: 3
            $fieldName2 => 5, // JavaScript: 5
            $fieldName3 => 4, // Python: 4
        ];

        $response = $this->actingAs($user)->post('/assessment', $assessmentData);
        $response->assertRedirect('/assessment');
        $response->assertSessionHas('success');

        // Verify mean score calculation: (3 + 5 + 4) / 3 = 4
        $programmingScore = StudentScore::where('student_id', $student->id)
            ->where('sub_category_id', $programmingSubcategory->id)
            ->first();
        
        $this->assertNotNull($programmingScore);
        $this->assertEquals(4, $programmingScore->score); // Mean of 3, 5, 4 = 4
    }
} 