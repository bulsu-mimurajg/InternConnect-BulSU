<?php

use App\Models\User;
use App\Models\AcademeAccount;
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

    // Create a section for the test
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    $response = $this->post('/register', [
        'username' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'section_id' => $section->section_id,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('status', 'Registration successful! Please log in to continue.');
});

test('registration saves section to academe_accounts', function () {
    // Create a section
    $section = Section::create([
        'section_name' => 'BSIT-1A',
        'status' => 'active'
    ]);

    $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'section_id' => $section->section_id,
    ]);

    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('status', 'Registration successful! Please log in to continue.');
    
    // Check that user was created
    $user = User::where('email', 'test@example.com')->first();
    $this->assertNotNull($user);
    
    // Check that user has student role
    $this->assertTrue($user->hasRole('student'));
    
    // Check that academe account was created with correct section
    $academeAccount = $user->academeAccounts()->first();
    $this->assertNotNull($academeAccount);
    $this->assertEquals($section->section_id, $academeAccount->section_id);
    
    // Check that request was created
    $request = RequestModel::where('stud_num', 'testuser')->first();
    $this->assertNotNull($request);
    $this->assertEquals($section->section_id, $request->section_id);
});

test('registration requires valid section_id', function () {
    $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
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
