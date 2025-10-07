<?php

use App\Models\User;
use App\Models\Section;
use App\Models\AcademeAccount;
use Database\Seeders\RolePermissionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
//    $this->withoutExceptionHandling();

    $user = User::factory()->create([
        'status' => 'verified'
    ]);

    $user->assignRole('student');

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('student.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'status' => 'verified'
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create([
        'status' => 'verified'
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('unverified users can not authenticate', function () {
    $user = User::factory()->create([
        'status' => 'unverified'
    ]);

    $user->assignRole('student');

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['username']);
    $response->assertSessionHasErrors(['username' => 'Your account is not yet verified. Please contact your adviser.']);
});

test('archived users can not authenticate', function () {
    $user = User::factory()->create([
        'status' => 'archived'
    ]);

    $user->assignRole('student');

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['username']);
    $response->assertSessionHasErrors(['username' => 'Kindly contact the administrator for further assistance.']);
});

test('students can not authenticate when their section is archived', function () {
    // Create an archived section
    $section = Section::create([
        'section_name' => 'CS-3A',
        'status' => 'archived'
    ]);

    // Create a verified student user
    $user = User::factory()->create([
        'status' => 'verified'
    ]);

    $user->assignRole('student');

    // Create academe account linking user to archived section
    AcademeAccount::create([
        'user_id' => $user->id,
        'section_id' => $section->section_id
    ]);

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['username']);
    $response->assertSessionHasErrors(['username' => 'Unable to login, section is archived. Please contact your administrator.']);
});

test('students can authenticate when their section is active', function () {
    // Create an active section
    $section = Section::create([
        'section_name' => 'CS-3A',
        'status' => 'active'
    ]);

    // Create a verified student user
    $user = User::factory()->create([
        'status' => 'verified'
    ]);

    $user->assignRole('student');

    // Create academe account linking user to active section
    AcademeAccount::create([
        'user_id' => $user->id,
        'section_id' => $section->section_id
    ]);

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('student.dashboard', absolute: false));
});
