<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogAuthenticationActivity
{
    /**
     * Handle the login event.
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        $guard = $event->guard;
        
        // Get user role for better context
        $role = $user->getRoleNames()->first() ?? 'unknown';
        
        // Check if a login activity was already logged in the last 5 seconds to prevent duplicates
        $recentLogin = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)
            ->where('description', 'logged in')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->first();
            
        if ($recentLogin) {
            Log::info('Duplicate login event prevented', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);
            return;
        }
        
        // Log the login activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $role,
                'guard' => $guard,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_time' => now()->toISOString(),
            ])
            ->log('logged in');
            
        Log::info('User logged in', [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $role,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Handle the logout event.
     */
    public function handleLogout(Logout $event): void
    {
        $user = $event->user;
        $guard = $event->guard;
        
        // Get user role for better context
        $role = $user->getRoleNames()->first() ?? 'unknown';
        
        // Check if a logout activity was already logged in the last 5 seconds to prevent duplicates
        $recentLogout = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)
            ->where('description', 'logged out')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->first();
            
        if ($recentLogout) {
            Log::info('Duplicate logout event prevented', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);
            return;
        }
        
        // Log the logout activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $role,
                'guard' => $guard,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logout_time' => now()->toISOString(),
            ])
            ->log('logged out');
            
        Log::info('User logged out', [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $role,
            'ip_address' => request()->ip(),
        ]);
    }
}
