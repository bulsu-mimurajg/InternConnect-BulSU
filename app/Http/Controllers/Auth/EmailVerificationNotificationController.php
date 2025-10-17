<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
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

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
