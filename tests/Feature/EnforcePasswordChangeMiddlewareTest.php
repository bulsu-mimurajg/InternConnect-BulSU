<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('redirects users who must change password', function () {
    // Create a user who must change password
    $user = User::factory()->create([
        'must_change_password' => true,
    ]);
    
    // Authenticate the user
    Auth::login($user);
    
    // Try to access a protected route
    $response = $this->get('/admin/dashboard');
    
    // Should redirect to password change page
    expect($response->status())->toBe(302);
    expect($response->headers->get('Location'))->toContain('/change-password');
});

it('allows access to password change page when password change is required', function () {
    // Create a user who must change password
    $user = User::factory()->create([
        'must_change_password' => true,
    ]);
    
    // Authenticate the user
    Auth::login($user);
    
    // Try to access password change page
    $response = $this->get('/change-password');
    
    // Should allow access
    expect($response->status())->toBe(200);
});

it('allows normal access when password change is not required', function () {
    // Create a user who doesn't need to change password
    $user = User::factory()->create([
        'must_change_password' => false,
    ]);
    
    // Authenticate the user
    Auth::login($user);
    
    // Try to access a protected route
    $response = $this->get('/admin/dashboard');
    
    // Should not redirect to password change page
    expect($response->headers->get('Location'))->not->toContain('/change-password');
});
