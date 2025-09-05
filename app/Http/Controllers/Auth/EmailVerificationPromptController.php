<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Show the email verification prompt page.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            $user = $request->user();
            if ($user->hasRole('admin')) {
                return redirect()->intended(route('student-list', absolute: false));
            } elseif ($user->hasRole('hte')) {
                return redirect()->intended(route('hte.dashboard', absolute: false));
            } elseif ($user->hasRole('adviser')) {
                return redirect()->intended(route('adviser.dashboard', absolute: false));
            } elseif ($user->hasRole('student')) {
                return redirect()->intended(route('student.dashboard', absolute: false));
            }
            return redirect()->intended(route('home', absolute: false));
        }
        
        return Inertia::render('auth/verify-email', ['status' => $request->session()->get('status')]);
    }
}
