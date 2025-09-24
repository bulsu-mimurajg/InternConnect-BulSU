<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Activitylog\Models\Activity;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('login activity is logged for admin users', function () {
    $admin = User::factory()->create([
        'status' => 'verified'
    ]);
    $admin->assignRole('admin');

    $this->post('/login', [
        'username' => $admin->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    // Check that login activity was logged
    $activity = Activity::where('causer_id', $admin->id)
        ->where('description', 'logged in')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties)->toHaveKey('role', 'admin');
    expect($activity->properties)->toHaveKey('username', $admin->username);
    expect($activity->properties)->toHaveKey('email', $admin->email);
});

test('login activity is logged for student users', function () {
    $student = User::factory()->create([
        'status' => 'verified'
    ]);
    $student->assignRole('student');

    $this->post('/login', [
        'username' => $student->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    // Check that login activity was logged
    $activity = Activity::where('causer_id', $student->id)
        ->where('description', 'logged in')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties)->toHaveKey('role', 'student');
    expect($activity->properties)->toHaveKey('username', $student->username);
    expect($activity->properties)->toHaveKey('email', $student->email);
});

test('login activity is logged for hte users', function () {
    $hte = User::factory()->create([
        'status' => 'verified'
    ]);
    $hte->assignRole('hte');

    $this->post('/login', [
        'username' => $hte->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    // Check that login activity was logged
    $activity = Activity::where('causer_id', $hte->id)
        ->where('description', 'logged in')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties)->toHaveKey('role', 'hte');
    expect($activity->properties)->toHaveKey('username', $hte->username);
    expect($activity->properties)->toHaveKey('email', $hte->email);
});

test('login activity is logged for adviser users', function () {
    $adviser = User::factory()->create([
        'status' => 'verified'
    ]);
    $adviser->assignRole('adviser');

    $this->post('/login', [
        'username' => $adviser->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    // Check that login activity was logged
    $activity = Activity::where('causer_id', $adviser->id)
        ->where('description', 'logged in')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties)->toHaveKey('role', 'adviser');
    expect($activity->properties)->toHaveKey('username', $adviser->username);
    expect($activity->properties)->toHaveKey('email', $adviser->email);
});

test('logout activity is logged for all user types', function () {
    $user = User::factory()->create([
        'status' => 'verified'
    ]);
    $user->assignRole('student');

    // Login first
    $this->actingAs($user);

    // Logout
    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');

    // Check that logout activity was logged
    $activity = Activity::where('causer_id', $user->id)
        ->where('description', 'logged out')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties)->toHaveKey('role', 'student');
    expect($activity->properties)->toHaveKey('username', $user->username);
    expect($activity->properties)->toHaveKey('email', $user->email);
});

test('failed login attempts are not logged as successful logins', function () {
    $user = User::factory()->create([
        'status' => 'verified'
    ]);
    $user->assignRole('student');

    // Attempt login with wrong password
    $this->post('/login', [
        'username' => $user->username,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();

    // Check that no login activity was logged
    $activity = Activity::where('causer_id', $user->id)
        ->where('description', 'logged in')
        ->first();

    expect($activity)->toBeNull();
});

test('unverified users cannot login and no activity is logged', function () {
    $user = User::factory()->create([
        'status' => 'unverified'
    ]);
    $user->assignRole('student');

    $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest();

    // Check that no login activity was logged
    $activity = Activity::where('causer_id', $user->id)
        ->where('description', 'logged in')
        ->first();

    expect($activity)->toBeNull();
});
