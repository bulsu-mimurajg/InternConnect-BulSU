<?php

namespace App\Services;

use App\Models\User;

class UserUniquenessService
{
    /**
     * Check if username is available (no active user has it)
     */
    public static function isUsernameAvailable(string $username): bool
    {
        return !User::where('username', $username)
            ->whereIn('status', ['verified', 'unverified'])
            ->exists();
    }
    
    /**
     * Check if email is available (no active user has it)
     */
    public static function isEmailAvailable(string $email): bool
    {
        return !User::where('email', $email)
            ->whereIn('status', ['verified', 'unverified'])
            ->exists();
    }
    
    /**
     * Get active user by username (excluding archived)
     */
    public static function getActiveUserByUsername(string $username): ?User
    {
        return User::where('username', $username)
            ->whereIn('status', ['verified', 'unverified'])
            ->first();
    }
}
