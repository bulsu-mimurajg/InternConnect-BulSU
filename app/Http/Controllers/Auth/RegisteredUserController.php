<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use App\Models\Section;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        $sections = Section::where('status', 'active')->get(['section_id', 'section_name']);
        
        return Inertia::render('auth/register', [
            'sections' => $sections
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Check if user with this email already exists and is inactive
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $existingUser->status === 'inactive') {
            return back()->withErrors([
                'email' => 'This email is associated with an inactive account. Please contact the administrator.'
            ])->withInput();
        }

        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'section_id' => 'required|exists:sections,section_id',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('student');

        // Create a request record for the student
        RequestModel::create([
            'stud_num' => $request->username, // Using username as student number
            'section_id' => $request->section_id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('assessment', absolute: false));
    }
}
