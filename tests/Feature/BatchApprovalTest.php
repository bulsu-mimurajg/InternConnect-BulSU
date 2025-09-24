<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Student;
use App\Models\Internship;
use App\Models\HTE;
use App\Models\StudentMatch;
use App\Models\StudentPlacement;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BatchApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        
        // Create a test user with admin role
        $user = User::factory()->admin()->create();
        
        Auth::login($user);
    }

    public function test_batch_approval_conflicts_shows_correct_fallback_internships()
    {
        // Create a section
        $section = Section::create([
            'section_name' => 'Test Section',
            'status' => 'active'
        ]);

        // Create HTE user and HTE
        $hteUser = User::factory()->hte()->create();
        $hte = HTE::create([
            'user_id' => $hteUser->id,
            'company_name' => 'Test Company',
            'contact_person' => 'Test Person',
            'email' => 'test@company.com',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'status' => 'active',
            'is_submit' => true
        ]);

        // Create internships with limited slots
        $internship1 = Internship::create([
            'position_title' => 'Software Development Intern',
            'department' => 'IT',
            'hte_id' => $hte->id,
            'placement_description' => 'Software development internship description',
            'slot_count' => 2, // Only 2 slots
            'is_active' => true
        ]);

        $internship2 = Internship::create([
            'position_title' => 'Marketing Intern',
            'department' => 'Marketing',
            'hte_id' => $hte->id,
            'placement_description' => 'Marketing internship description',
            'slot_count' => 3, // 3 slots
            'is_active' => true
        ]);

        $internship3 = Internship::create([
            'position_title' => 'HR Intern',
            'department' => 'HR',
            'hte_id' => $hte->id,
            'placement_description' => 'HR internship description',
            'slot_count' => 2, // 2 slots
            'is_active' => true
        ]);

        // Create student users and students manually
        $studentUser1 = User::factory()->student()->create();
        $student1 = Student::create([
            'user_id' => $studentUser1->id,
            'student_number' => 'STU001',
            'first_name' => 'Ana',
            'middle_name' => 'M',
            'last_name' => 'Martinez',
            'phone' => '1234567890',
            'section_id' => $section->section_id,
            'specialization' => 'Computer Science',
            'address' => 'Test Address 1',
            'birth_date' => '2000-01-01',
            'is_submit' => true,
            'is_active' => true,
            'is_placed' => false
        ]);

        $studentUser2 = User::factory()->student()->create();
        $student2 = Student::create([
            'user_id' => $studentUser2->id,
            'student_number' => 'STU002',
            'first_name' => 'John',
            'middle_name' => 'J',
            'last_name' => 'Doe',
            'phone' => '1234567891',
            'section_id' => $section->section_id,
            'specialization' => 'Computer Science',
            'address' => 'Test Address 2',
            'birth_date' => '2000-01-02',
            'is_submit' => true,
            'is_active' => true,
            'is_placed' => false
        ]);

        $studentUser3 = User::factory()->student()->create();
        $student3 = Student::create([
            'user_id' => $studentUser3->id,
            'student_number' => 'STU003',
            'first_name' => 'Jane',
            'middle_name' => 'J',
            'last_name' => 'Smith',
            'phone' => '1234567892',
            'section_id' => $section->section_id,
            'specialization' => 'Computer Science',
            'address' => 'Test Address 3',
            'birth_date' => '2000-01-03',
            'is_submit' => true,
            'is_active' => true,
            'is_placed' => false
        ]);

        // Create student matches
        // Student 1: Best match is Software Development (97.8%), fallback is Marketing (85.2%)
        StudentMatch::create([
            'student_id' => $student1->id,
            'internship_id' => $internship1->id,
            'rank' => 1,
            'compatibility_score' => 97.8,
            'status' => 'pending'
        ]);

        StudentMatch::create([
            'student_id' => $student1->id,
            'internship_id' => $internship2->id,
            'rank' => 2,
            'compatibility_score' => 85.2,
            'status' => 'pending'
        ]);

        // Student 2: Best match is Software Development (95.5%), fallback is Marketing (82.1%)
        StudentMatch::create([
            'student_id' => $student2->id,
            'internship_id' => $internship1->id,
            'rank' => 1,
            'compatibility_score' => 95.5,
            'status' => 'pending'
        ]);

        StudentMatch::create([
            'student_id' => $student2->id,
            'internship_id' => $internship2->id,
            'rank' => 2,
            'compatibility_score' => 82.1,
            'status' => 'pending'
        ]);

        // Student 3: Best match is Software Development (93.2%), fallback is Marketing (78.9%)
        StudentMatch::create([
            'student_id' => $student3->id,
            'internship_id' => $internship1->id,
            'rank' => 1,
            'compatibility_score' => 93.2,
            'status' => 'pending'
        ]);

        StudentMatch::create([
            'student_id' => $student3->id,
            'internship_id' => $internship2->id,
            'rank' => 2,
            'compatibility_score' => 78.9,
            'status' => 'pending'
        ]);

        // Test the batch conflict check
        $response = $this->postJson('/student/check-batch-conflicts', [
            'student_ids' => [$student1->id, $student2->id, $student3->id]
        ]);

        $response->assertStatus(200);
        $responseData = $response->json();

        // Should have conflicts since 3 students want 2 slots in Software Development
        $this->assertTrue($responseData['has_conflicts']);
        $this->assertCount(1, $responseData['conflicts']); // 1 student should need fallback

        // Check that the fallback internship is correct
        // Note: Jane Smith should need fallback since she has the lowest compatibility score (93.2%)
        // Ana (97.8%) and John (95.5%) should get the 2 available slots
        $conflict = $responseData['conflicts'][0];
        $this->assertEquals('Jane Smith', $conflict['student_name']);
        $this->assertEquals('Software Development Intern', $conflict['best_match']['position_title']);
        $this->assertEquals('Marketing Intern', $conflict['fallback_match']['position_title']);
        $this->assertEquals(78.9, $conflict['fallback_match']['compatibility_score']);
    }

    public function test_batch_approval_with_internship_filter()
    {
        // Create a section
        $section = Section::create([
            'section_name' => 'Test Section',
            'status' => 'active'
        ]);

        // Create HTE user and HTE
        $hteUser = User::factory()->hte()->create();
        $hte = HTE::create([
            'user_id' => $hteUser->id,
            'company_name' => 'Test Company',
            'contact_person' => 'Test Person',
            'email' => 'test@company.com',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'status' => 'active',
            'is_submit' => true
        ]);

        // Create internships
        $internship1 = Internship::create([
            'position_title' => 'Software Development Intern',
            'department' => 'IT',
            'hte_id' => $hte->id,
            'placement_description' => 'Software development internship description',
            'slot_count' => 2,
            'is_active' => true
        ]);

        $internship2 = Internship::create([
            'position_title' => 'Marketing Intern',
            'department' => 'Marketing',
            'hte_id' => $hte->id,
            'placement_description' => 'Marketing internship description',
            'slot_count' => 3,
            'is_active' => true
        ]);

        // Create students
        $studentUser1 = User::factory()->student()->create();
        $student1 = Student::create([
            'user_id' => $studentUser1->id,
            'student_number' => 'STU001',
            'first_name' => 'Ana',
            'middle_name' => 'M',
            'last_name' => 'Martinez',
            'phone' => '1234567890',
            'section_id' => $section->section_id,
            'specialization' => 'Computer Science',
            'address' => 'Test Address 1',
            'birth_date' => '2000-01-01',
            'is_submit' => true,
            'is_active' => true,
            'is_placed' => false
        ]);

        $studentUser2 = User::factory()->student()->create();
        $student2 = Student::create([
            'user_id' => $studentUser2->id,
            'student_number' => 'STU002',
            'first_name' => 'John',
            'middle_name' => 'J',
            'last_name' => 'Doe',
            'phone' => '1234567891',
            'section_id' => $section->section_id,
            'specialization' => 'Computer Science',
            'address' => 'Test Address 2',
            'birth_date' => '2000-01-02',
            'is_submit' => true,
            'is_active' => true,
            'is_placed' => false
        ]);

        // Create student matches
        // Student 1: Best match is Software Development (97.8%), has Marketing match (85.2%)
        StudentMatch::create([
            'student_id' => $student1->id,
            'internship_id' => $internship1->id,
            'rank' => 1,
            'compatibility_score' => 97.8,
            'status' => 'pending'
        ]);

        StudentMatch::create([
            'student_id' => $student1->id,
            'internship_id' => $internship2->id,
            'rank' => 2,
            'compatibility_score' => 85.2,
            'status' => 'pending'
        ]);

        // Student 2: Best match is Software Development (95.5%), has Marketing match (82.1%)
        StudentMatch::create([
            'student_id' => $student2->id,
            'internship_id' => $internship1->id,
            'rank' => 1,
            'compatibility_score' => 95.5,
            'status' => 'pending'
        ]);

        StudentMatch::create([
            'student_id' => $student2->id,
            'internship_id' => $internship2->id,
            'rank' => 2,
            'compatibility_score' => 82.1,
            'status' => 'pending'
        ]);

        // Test batch approval with internship filter (Marketing)
        $response = $this->postJson('/student/batch-approve-placements', [
            'student_ids' => [$student1->id, $student2->id],
            'internship_filter' => $internship2->id // Filter for Marketing internship
        ]);

        $response->assertStatus(200);
        $responseData = $response->json();

        // Should successfully approve both students for Marketing internship
        $this->assertEquals(2, $responseData['total_approved']);
        $this->assertCount(0, $responseData['errors']);

        // Verify that both students were placed in Marketing internship (not their best match)
        $placement1 = StudentPlacement::where('student_id', $student1->id)->first();
        $placement2 = StudentPlacement::where('student_id', $student2->id)->first();

        $this->assertNotNull($placement1);
        $this->assertNotNull($placement2);
        $this->assertEquals($internship2->id, $placement1->internship_id);
        $this->assertEquals($internship2->id, $placement2->internship_id);
        $this->assertEquals('approved', $placement1->status);
        $this->assertEquals('approved', $placement2->status);
    }
}
