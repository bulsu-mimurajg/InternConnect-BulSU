<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('guests are redirected to the login page', function () {
    $this->get('/assessment')->assertRedirect('/login');
});

test('authenticated admin can visit the assessment', function () {
    $user = User::factory()->create();

    $user->assignRole('admin');

    $this->actingAs($user);

    $this->get('/student/list')->assertOk();
});

test('authenticated hte can visit the assessment', function () {
    $user = User::factory()->create();

    $user->assignRole('hte');

    $this->actingAs($user);

    $this->get('/form')->assertOk();
});


test('authenticated student can visit the assessment', function () {
    $user = User::factory()->create();

    $user->assignRole('student');

    $this->actingAs($user);

    $this->get('/assessment')->assertOk();
});
