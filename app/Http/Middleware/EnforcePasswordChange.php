<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnforcePasswordChange Middleware
 * 
 * This middleware ensures that users who must change their password
 * cannot access any part of the application except:
 * - Password change page (/change-password)
 * - Logout functionality
 * - Static assets (CSS, JS, images, etc.)
 * 
 * This prevents users from bypassing the password change requirement
 * by directly accessing other pages.
 */
class EnforcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user must change password
            if ($user->mustChangePassword()) {
                // Allow access to specific routes
                $allowedRoutes = [
                    'password.change',
                    'password.change.store',
                    'logout',
                ];
                
                // Allow access to static assets and API routes
                $allowedPaths = [
                    '/css/',
                    '/js/',
                    '/images/',
                    '/favicon.ico',
                    '/api/',
                ];
                
                $currentPath = $request->path();
                $currentRoute = $request->route()?->getName();
                
                // Check if current path starts with any allowed path
                $isAllowedPath = collect($allowedPaths)->contains(function ($path) use ($currentPath) {
                    return str_starts_with($currentPath, trim($path, '/'));
                });
                
                // Allow access to password change related routes and static assets
                if (!in_array($currentRoute, $allowedRoutes) && !$isAllowedPath) {
                    return redirect()->route('password.change');
                }
            }
        }

        return $next($request);
    }
}