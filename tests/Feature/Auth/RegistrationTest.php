<?php

use App\Models\User;
use App\Models\AcademeAccount;
use App\Models\InternshipSeason;
use App\Models\Section;
use App\Models\Request as RequestModel;
use Database\Seeders\RolePermissionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
//    $this->withoutExceptionHandling();

    // Create an active internship season
    InternshipSeason::create([
        'name' => 'Test Season 2024',
        'start_date' => now()->subDays(30),
        'end_date' => now()->addDays(30),
        'status' => 'active'
    ]);

    // Create a section for the test
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    $response = $this->post('/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'middle_name' => 'M',
        'username' => '2022100123',
        'email' => 'test@example.com',
        'password' => '@Pass123',
        'password_confirmation' => '@Pass123',
        'contact_number' => '09123456789',
        'specialization' => 'BA',
        'section_id' => $section->section_id,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('status', 'Registration successful! Please check your email and click the verification link to complete your registration.');
});

//test('registration saves section to academe_accounts', function () {
//    // Create a section
//    $section = Section::create([
//        'section_name' => 'BSIT-1A',
//        'status' => 'active'
//    ]);
//
//    $response = $this->post('/register', [
//        'username' => '2022100123',
//        'email' => 'test@example.com',
//        'password' => '@Pass123',
//        'password_confirmation' => '@Pass123',
//        'section_id' => $section->section_id,
//    ]);
//
//    $response->assertRedirect(route('login', absolute: false));
//    $response->assertSessionHas('status', 'Registration successful! Please check your email and click the verification link to complete your registration.');
//
//    // Check that user was created
//    $user = User::where('email', 'test@example.com')->first();
//    $this->assertNotNull($user);
//
//    // Check that user has student role
//    $this->assertTrue($user->hasRole('student'));
//
//    // Check that academe account was created with correct section
//    $academeAccount = $user->academeAccounts()->first();
//    $this->assertNotNull($academeAccount);
//    $this->assertEquals($section->section_id, $academeAccount->section_id);
//
//    // Check that request was created
//    $request = RequestModel::where('stud_num', 'testuser')->first();
//    $this->assertNotNull($request);
//    $this->assertEquals($section->section_id, $request->section_id);
//});

test('registration requires valid section_id', function () {
    // Create an active internship season
    InternshipSeason::create([
        'name' => 'Test Season 2024',
        'start_date' => now()->subDays(30),
        'end_date' => now()->addDays(30),
        'status' => 'active'
    ]);

    $response = $this->post('/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'middle_name' => 'M',
        'username' => '2022100123',
        'email' => 'test@example.com',
        'password' => '@Pass123',
        'password_confirmation' => '@Pass123',
        'contact_number' => '09123456789',
        'specialization' => 'BA',
        'section_id' => 999, // Non-existent section
    ]);

    $response->assertSessionHasErrors(['section_id']);

    // Check that no user was created
    $this->assertDatabaseMissing('users', [
        'email' => 'test@example.com'
    ]);

    // Check that no academe account was created
    $this->assertDatabaseCount('academe_accounts', 0);
});

test('registration screen shows closed message when no active season', function () {
    // Ensure no active season exists
    InternshipSeason::where('status', 'active')->update(['status' => 'completed']);
    
    $response = $this->get('/register');
    
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('auth/register')
            ->where('hasActiveSeason', false)
            ->where('activeSeason', null)
            ->where('message', 'Registration is currently closed. No active internship season is available.')
    );
});

test('registration is blocked when no active season exists', function () {
    // Ensure no active season exists
    InternshipSeason::where('status', 'active')->update(['status' => 'completed']);
    
    // Create a section for the test
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    $response = $this->post('/register', [
        'username' => '2022100123',
        'email' => 'test@example.com',
        'password' => '@Pass123',
        'password_confirmation' => '@Pass123',
        'section_id' => $section->section_id,
    ]);

    $response->assertSessionHasErrors(['season']);
    
    // Check that no user was created
    $this->assertDatabaseMissing('users', [
        'email' => 'test@example.com'
    ]);
});

test('registration works when active season exists', function () {
    // Create an active internship season
    InternshipSeason::create([
        'name' => 'Test Season 2024',
        'start_date' => now()->subDays(30),
        'end_date' => now()->addDays(30),
        'status' => 'active'
    ]);
    
    // Create a section for the test
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    $response = $this->post('/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'middle_name' => 'M',
        'username' => '2022100123',
        'email' => 'test@example.com',
        'password' => '@Pass123',
        'password_confirmation' => '@Pass123',
        'contact_number' => '09123456789',
        'specialization' => 'BA',
        'section_id' => $section->section_id,
    ]);

    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('status', 'Registration successful! Please check your email and click the verification link to complete your registration.');
});

test('registration screen shows active season info when season exists', function () {
    // Create an active internship season
    $season = InternshipSeason::create([
        'name' => 'Test Season 2024',
        'start_date' => now()->subDays(30),
        'end_date' => now()->addDays(30),
        'status' => 'active'
    ]);
    
    $response = $this->get('/register');
    
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->component('auth/register')
            ->where('hasActiveSeason', true)
            ->where('activeSeason.name', $season->name)
            ->where('message', null)
    );
});
