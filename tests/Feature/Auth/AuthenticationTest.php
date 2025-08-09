<?php

use App\Models\User;
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
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('assessment', absolute: false));
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
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['email']);
    $response->assertSessionHasErrors(['email' => 'Your account is not yet verified. Please contact an administrator.']);
});

test('archived users can not authenticate', function () {
    $user = User::factory()->create([
        'status' => 'archived'
    ]);

    $user->assignRole('student');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['email']);
    $response->assertSessionHasErrors(['email' => 'Your account is not yet verified. Please contact an administrator.']);
});
