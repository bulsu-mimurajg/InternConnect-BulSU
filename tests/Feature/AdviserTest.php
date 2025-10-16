<?php

use App\Models\User;
use App\Models\AcademeAccount;
use App\Models\Adviser;
use App\Models\Section;
use App\Models\Student;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\AcademeAccountSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('adviser can view dashboard', function () {
    // Create section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    // Create adviser
    $adviser = User::factory()->create();
    $adviser->assignRole('adviser');
    
    // Assign adviser to section
    Adviser::create([
        'user_id' => $adviser->id,
        'section_id' => $section->section_id,
        'adviser_fname' => 'Test',
        'adviser_lname' => 'Adviser',
        'is_active' => true,
    ]);

    // Create students in same section
    $students = User::factory()->student()->count(3)->create();
    foreach ($students as $student) {
        AcademeAccount::create([
            'user_id' => $student->id,
            'section_id' => $section->section_id,
        ]);
    }

    $response = $this->actingAs($adviser)->get('/adviser/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('adviser/dashboard')
            ->has('stats')
            ->has('recentAssessments')
            ->has('placementOverview')
            ->has('adviserSection')
    );
});

test('adviser can view students page', function () {
    // Create section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    // Create adviser
    $adviser = User::factory()->create();
    $adviser->assignRole('adviser');
    
    // Create adviser record
    $adviserRecord = Adviser::create([
        'user_id' => $adviser->id,
        'adviser_fname' => 'Test',
        'adviser_lname' => 'Adviser',
        'is_active' => true,
    ]);
    
    // Assign adviser to section using pivot table
    $adviserRecord->sections()->attach($section->section_id);

    // Create students in same section
    $students = User::factory()->student()->count(2)->create();
    foreach ($students as $student) {
        $student->update(['status' => 'verified']);
        AcademeAccount::create([
            'user_id' => $student->id,
            'section_id' => $section->section_id,
        ]);
        
        // Create Student record
        Student::create([
            'user_id' => $student->id,
            'student_number' => $student->username,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'middle_name' => '',
            'phone' => '',
            'section_id' => $section->section_id,
            'specialization' => '',
            'address' => '',
            'birth_date' => now()->format('Y-m-d'),
            'is_submit' => false,
        ]);
    }

    $response = $this->actingAs($adviser)->get('/adviser/student-list');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('adviser/students')
            ->has('students', 2)
            ->has('adviserSection')
    );
});

test('adviser can view application page', function () {
    // Create section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    // Create adviser
    $adviser = User::factory()->create();
    $adviser->assignRole('adviser');
    
    // Create adviser record
    $adviserRecord = Adviser::create([
        'user_id' => $adviser->id,
        'adviser_fname' => 'Test',
        'adviser_lname' => 'Adviser',
        'is_active' => true,
    ]);
    
    // Assign adviser to section using pivot table
    $adviserRecord->sections()->attach($section->section_id);

    // Create students in same section
    $students = User::factory()->student()->count(3)->create();
    foreach ($students as $student) {
        $student->update(['status' => 'unverified']);
        AcademeAccount::create([
            'user_id' => $student->id,
            'section_id' => $section->section_id,
        ]);
    }

    $response = $this->actingAs($adviser)->get('/student-verification');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('adviser/application')
            ->has('pendingStudents', 3)
            ->has('verifiedStudents', 0)
            ->has('adviserSection')
    );
});

test('adviser can approve students', function () {
    // Create section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    // Create adviser
    $adviser = User::factory()->create();
    $adviser->assignRole('adviser');
    
    // Assign adviser to section
    Adviser::create([
        'user_id' => $adviser->id,
        'section_id' => $section->section_id,
        'adviser_fname' => 'Test',
        'adviser_lname' => 'Adviser',
        'is_active' => true,
    ]);

    // Create students in same section
    $students = User::factory()->student()->count(2)->create();
    $studentIds = [];
    foreach ($students as $student) {
        AcademeAccount::create([
            'user_id' => $student->id,
            'section_id' => $section->section_id,
        ]);
        $studentIds[] = $student->id;
    }

    $response = $this->actingAs($adviser)->post('/application/approve', [
        'studentIds' => $studentIds
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    // Check that student records were created and status updated
    $this->assertDatabaseCount('students', 2);
    foreach ($students as $student) {
        $student->refresh();
        $this->assertDatabaseHas('students', [
            'user_id' => $student->id,
            'student_number' => $student->username,
        ]);
        $this->assertEquals('verified', $student->status);
    }
});

test('adviser can reject students', function () {
    // Create section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    // Create adviser
    $adviser = User::factory()->create();
    $adviser->assignRole('adviser');
    
    // Assign adviser to section
    Adviser::create([
        'user_id' => $adviser->id,
        'section_id' => $section->section_id,
        'adviser_fname' => 'Test',
        'adviser_lname' => 'Adviser',
        'is_active' => true,
    ]);

    // Create students in same section
    $students = User::factory()->student()->count(2)->create();
    $studentIds = [];
    foreach ($students as $student) {
        AcademeAccount::create([
            'user_id' => $student->id,
            'section_id' => $section->section_id,
        ]);
        $studentIds[] = $student->id;
    }

    $response = $this->actingAs($adviser)->post('/application/reject', [
        'studentIds' => $studentIds
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    // Check that students status is set to archived
    foreach ($students as $student) {
        $student->refresh();
        $this->assertEquals('archived', $student->status);
    }
});

test('adviser can remove student access', function () {
    // Create section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    // Create adviser
    $adviser = User::factory()->create();
    $adviser->assignRole('adviser');
    
    // Assign adviser to section
    Adviser::create([
        'user_id' => $adviser->id,
        'section_id' => $section->section_id,
        'adviser_fname' => 'Test',
        'adviser_lname' => 'Adviser',
        'is_active' => true,
    ]);

    // Create verified students with student records
    $students = User::factory()->student()->count(2)->create(['status' => 'verified']);
    $studentIds = [];
    foreach ($students as $student) {
        AcademeAccount::create([
            'user_id' => $student->id,
            'section_id' => $section->section_id,
        ]);
        
        // Create student record
        Student::create([
            'user_id' => $student->id,
            'student_number' => $student->username,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'middle_name' => '',
            'phone' => '',
            'section_id' => $section->section_id,
            'specialization' => '',
            'address' => '',
            'birth_date' => now()->format('Y-m-d'),
            'is_submit' => false,
        ]);
        
        $studentIds[] = $student->id;
    }

    $response = $this->actingAs($adviser)->post('/application/remove-access', [
        'studentIds' => $studentIds
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    // Check that students have status set to unverified (but keep role and student record)
    foreach ($students as $student) {
        $student->refresh();
        $this->assertTrue($student->hasRole('student'));
        $this->assertEquals('unverified', $student->status);
        $this->assertDatabaseHas('students', ['user_id' => $student->id]);
    }
});

test('adviser can undo approve action', function () {
    // Create section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    // Create adviser
    $adviser = User::factory()->create();
    $adviser->assignRole('adviser');
    
    // Assign adviser to section
    Adviser::create([
        'user_id' => $adviser->id,
        'section_id' => $section->section_id,
        'adviser_fname' => 'Test',
        'adviser_lname' => 'Adviser',
        'is_active' => true,
    ]);

    // Create verified students with student records
    $students = User::factory()->student()->count(2)->create(['status' => 'verified']);
    $studentIds = [];
    foreach ($students as $student) {
        AcademeAccount::create([
            'user_id' => $student->id,
            'section_id' => $section->section_id,
        ]);
        
        // Create student record
        Student::create([
            'user_id' => $student->id,
            'student_number' => $student->username,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'middle_name' => '',
            'phone' => '',
            'section_id' => $section->section_id,
            'specialization' => '',
            'address' => '',
            'birth_date' => now()->format('Y-m-d'),
            'is_submit' => false,
        ]);
        
        $studentIds[] = $student->id;
    }

    $response = $this->actingAs($adviser)->post('/application/undo', [
        'action' => 'approve',
        'studentIds' => $studentIds
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    // Check that students status is back to unverified and student records are deleted
    foreach ($students as $student) {
        $student->refresh();
        $this->assertEquals('unverified', $student->status);
        $this->assertDatabaseMissing('students', ['user_id' => $student->id]);
    }
});

test('adviser can undo reject action', function () {
    // Create section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    // Create adviser
    $adviser = User::factory()->create();
    $adviser->assignRole('adviser');
    
    // Assign adviser to section
    Adviser::create([
        'user_id' => $adviser->id,
        'section_id' => $section->section_id,
        'adviser_fname' => 'Test',
        'adviser_lname' => 'Adviser',
        'is_active' => true,
    ]);

    // Create archived students
    $students = User::factory()->student()->count(2)->create(['status' => 'archived']);
    $studentIds = [];
    foreach ($students as $student) {
        AcademeAccount::create([
            'user_id' => $student->id,
            'section_id' => $section->section_id,
        ]);
        $studentIds[] = $student->id;
    }

    $response = $this->actingAs($adviser)->post('/application/undo', [
        'action' => 'reject',
        'studentIds' => $studentIds
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    // Check that students status is back to unverified
    foreach ($students as $student) {
        $student->refresh();
        $this->assertEquals('unverified', $student->status);
    }
});

test('adviser can undo remove access action', function () {
    // Create section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    // Create adviser
    $adviser = User::factory()->create();
    $adviser->assignRole('adviser');
    
    // Assign adviser to section
    Adviser::create([
        'user_id' => $adviser->id,
        'section_id' => $section->section_id,
        'adviser_fname' => 'Test',
        'adviser_lname' => 'Adviser',
        'is_active' => true,
    ]);

    // Create students with student role and unverified status (simulating after remove access)
    $students = User::factory()->student()->count(2)->create(['status' => 'unverified']);
    $studentIds = [];
    foreach ($students as $student) {
        AcademeAccount::create([
            'user_id' => $student->id,
            'section_id' => $section->section_id,
        ]);
        
        // Create student record (since remove access doesn't delete it)
        Student::create([
            'user_id' => $student->id,
            'student_number' => $student->username,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'middle_name' => '',
            'phone' => '',
            'section_id' => $section->section_id,
            'specialization' => '',
            'address' => '',
            'birth_date' => now()->format('Y-m-d'),
            'is_submit' => false,
        ]);
        
        $studentIds[] = $student->id;
    }

    $response = $this->actingAs($adviser)->post('/application/undo', [
        'action' => 'remove',
        'studentIds' => $studentIds
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    // Check that students have student role, verified status, and student records
    foreach ($students as $student) {
        $student->refresh();
        $this->assertTrue($student->hasRole('student'));
        $this->assertEquals('verified', $student->status);
        $this->assertDatabaseHas('students', ['user_id' => $student->id]);
    }
}); 