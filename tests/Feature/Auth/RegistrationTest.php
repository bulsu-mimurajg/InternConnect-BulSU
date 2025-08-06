<?php

use App\Models\Section;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SectionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));
beforeEach(fn () => $this->seed(SectionSeeder::class));

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
//    $this->withoutExceptionHandling();

    $response = $this->post('/register', [
        'username' => 'Test User',
        'email' => 'test@example.com',
        'section_id' => Section::first()->section_id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('assessment', absolute: false));
});
