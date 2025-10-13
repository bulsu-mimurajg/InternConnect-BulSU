<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AcademeAccount;
use App\Models\EmailVerificationAttempt;
use App\Models\Request as RequestModel;
use App\Models\Section;
use App\Models\User;
use App\Services\EmailService;
use Cache;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Rules\UniqueActiveStudentNumber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

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
        // Check rate limiting for email verification attempts
        if (!EmailVerificationAttempt::canAttemptVerification($request->email)) {
            $timeRemaining = EmailVerificationAttempt::getFormattedTimeUntilNextAttempt($request->email);
            return back()->withErrors([
                'email' => "Too many verification attempts. Please wait {$timeRemaining} before trying again."
            ])->withInput();
        }

        // Check if user with this email already exists and is inactive
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $existingUser->status === 'inactive') {
            return back()->withErrors([
                'email' => 'This email is associated with an inactive account. Please contact the administrator.'
            ])->withInput();
        }

        // Custom password validation - show all requirements at once
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'username' => ['required', 'digits:10', new UniqueActiveStudentNumber],
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'contact_number' => 'required|string|max:11',
            'section_id' => 'required|exists:sections,section_id',
            'specialization' => 'required|string|in:BA,WMAD,SM',
        ]);

        // Add password errors if any
        if (!empty($passwordErrors)) {
            return back()->withErrors(['password' => $passwordErrors])->withInput();
        }

        // Generate a unique verification token
        $verificationToken = Str::random(64);

        // Store registration data temporarily in cache (expires in 24 hours)
        $registrationData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'username' => $request->username,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'password' => Hash::make($request->password),
            'section_id' => $request->section_id,
            'specialization' => $request->specialization,
            'created_at' => now(),
        ];

        Cache::put("registration_verification_{$verificationToken}", $registrationData, now()->addHours(24));
        // Also store with email key for later access during adviser approval
        Cache::put("registration_data_{$request->email}", $registrationData, now()->addHours(24));

        // Debug: Log the registration data being cached
        Log::info('Registration Data Cached', [
            'token' => $verificationToken,
            'email' => $request->email,
            'username' => $request->username,
            'section_id' => $request->section_id
        ]);

        try {
            // Send verification email
            $this->emailService->sendAccountVerification(
                $request->email,
                $request->username,
                $verificationToken
            );

            // Record the verification attempt
            EmailVerificationAttempt::recordAttempt(
                $request->email,
                $request->ip(),
                $request->userAgent()
            );

            return redirect()->route('login')->with('status', 'Registration successful! Please check your email and click the verification link to complete your registration.');
        } catch (\Exception $e) {
            // If email fails, remove the cached data
            Cache::forget("registration_verification_{$verificationToken}");

            return back()->withErrors([
                'email' => 'Failed to send verification email. Please try again or contact support.'
            ])->withInput();
        }
    }
}
