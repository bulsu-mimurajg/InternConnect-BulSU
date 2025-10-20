<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ChangePasswordController extends Controller
{
    /**
     * Show the change password form.
     */
    public function show(): Response
    {
        return Inertia::render('auth/change-password');
    }

    /**
     * Handle the password change request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Custom password validation to match register page
        $passwordErrors = [];
        
        if (empty($request->password)) {
            $passwordErrors[] = 'Password is required.';
        } else {
            // Check all requirements and collect all missing ones
            if (strlen($request->password) < 8) {
                $passwordErrors[] = 'Password must be at least 8 characters long.';
            }
            if (!preg_match('/[A-Z]/', $request->password)) {
                $passwordErrors[] = 'Password must contain at least one uppercase letter.';
            }
            if (!preg_match('/[a-z]/', $request->password)) {
                $passwordErrors[] = 'Password must contain at least one lowercase letter.';
            }
            if (!preg_match('/[0-9]/', $request->password)) {
                $passwordErrors[] = 'Password must contain at least one number.';
            }
            if (!preg_match('/[@$!%*?&]/', $request->password)) {
                $passwordErrors[] = 'Password must contain at least one special character (@$!%*?&).';
            }
        }

        if ($request->password !== $request->password_confirmation) {
            $passwordErrors[] = 'Password confirmation does not match.';
        }

        // Validate other fields
        $request->validate([
            'password' => 'required',
            'password_confirmation' => 'required',
        ]);

        // Add password errors if any
        if (!empty($passwordErrors)) {
            // Join multiple errors with newlines for display
            $errorMessage = implode("\n", $passwordErrors);
            return redirect()->route('password.change')->withErrors(['password' => $errorMessage])->withInput();
        }

        $user = Auth::user();
        
        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Mark password as changed
        $user->markPasswordChanged();

        // Redirect to appropriate dashboard based on user role
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')->with('success', 'Password changed successfully.');
        }

        if ($user->hasRole('hte')) {
            return redirect()->route('hte.dashboard')->with('success', 'Password changed successfully.');
        }

        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard')->with('success', 'Password changed successfully.');
        }

        if ($user->hasRole('adviser')) {
            return redirect()->route('adviser.dashboard')->with('success', 'Password changed successfully.');
        }

        return redirect()->route('home')->with('success', 'Password changed successfully.');
    }
}