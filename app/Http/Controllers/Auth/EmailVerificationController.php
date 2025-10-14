<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AcademeAccount;
use App\Models\EmailVerificationAttempt;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification page
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('login')->withErrors([
                'verification' => 'Invalid verification link.'
            ]);
        }

        // Check if token exists in cache
        $registrationData = Cache::get("registration_verification_{$token}");

        if (!$registrationData) {
            return redirect()->route('login')->withErrors([
                'verification' => 'Verification link has expired or is invalid. Please register again.'
            ]);
        }

        return Inertia::render('auth/verify-email', [
            'token' => $token,
            'email' => $registrationData['email']
        ]);
    }

    /**
     * Handle email verification
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $token = $request->token;

        // Get registration data from cache
        $registrationData = Cache::get("registration_verification_{$token}");

        // Debug: Log the token and cache data
        Log::info('Email Verification Debug', [
            'token' => $token,
            'has_registration_data' => !is_null($registrationData),
            'registration_data' => $registrationData ? array_keys($registrationData) : null
        ]);

        if (!$registrationData) {
            return redirect()->route('login')->withErrors([
                'verification' => 'Verification link has expired or is invalid. Please register again.'
            ]);
        }

        try {
            DB::beginTransaction();

            // Check if a non-archived user already exists with this username
            $existingActiveUser = User::where('username', $registrationData['username'])
                ->whereIn('status', ['verified', 'unverified'])
                ->first();
                
            if ($existingActiveUser) {
                // Active user exists - this shouldn't happen if validation worked correctly
                DB::rollBack();
                Cache::forget("registration_verification_{$token}");
                Log::warning('Email verification blocked: active user exists', [
                    'username' => $registrationData['username'],
                    'existing_user_id' => $existingActiveUser->id,
                ]);
                return redirect()->route('login')->withErrors([
                    'verification' => 'This student number is already registered with an active account.'
                ]);
            }

            // Check if email is already in use by a non-archived user
            $existingEmailUser = User::where('email', $registrationData['email'])
                ->whereIn('status', ['verified', 'unverified'])
                ->first();
                
            if ($existingEmailUser) {
                DB::rollBack();
                Cache::forget("registration_verification_{$token}");
                return redirect()->route('login')->withErrors([
                    'verification' => 'This email is already registered.'
                ]);
            }

            // Create a NEW user account (fresh start for returning students)
            $user = User::create([
                'username' => $registrationData['username'],
                'email' => $registrationData['email'],
                'password' => $registrationData['password'],
                'email_verified_at' => now(),
                'status' => 'unverified',
            ]);

            Log::info('Created new user account', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'is_returning_student' => User::where('username', $registrationData['username'])
                    ->where('status', 'archived')
                    ->exists(),
            ]);

            $user->assignRole('student');

            // Create an academe account record for the student with their selected section
            $user->academeAccounts()->create([
                'section_id' => $registrationData['section_id'],
            ]);

            // Create a request record for the student
            RequestModel::create([
                'stud_num' => $registrationData['username'], // Using username as student number
                'section_id' => $registrationData['section_id'],
            ]);

            // Notify advisers about new student registration (waiting for approval)
            $this->notifyAdvisersOfNewRegistration($user, $registrationData);

            DB::commit();

            // Remove the token-based cache but keep email-based cache for adviser approval
            Cache::forget("registration_verification_{$token}");
            // Keep "registration_data_{email}" cache for adviser approval

            // Reset verification attempts since email was successfully verified
            EmailVerificationAttempt::resetAttempts($registrationData['email']);

            return redirect()->route('login')->with('status', 'Email verified successfully! Your account has been created and is pending adviser approval. You will be notified once approved.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('login')->withErrors([
                'verification' => 'Failed to complete registration. Please try again or contact support.'
            ]);
        }
    }

    /**
     * Notify advisers about new student registration waiting for approval
     */
    private function notifyAdvisersOfNewRegistration($user, $registrationData)
    {
        try {
            // Get the section for the student
            $section = \App\Models\Section::find($registrationData['section_id']);
            if (!$section) {
                \Log::warning("Section not found for student registration: {$registrationData['section_id']}");
                return;
            }

            // Get all advisers for this section
            $advisers = \App\Models\User::role('adviser')
                ->where('status', 'verified')
                ->whereHas('adviser.sections', function($query) use ($registrationData) {
                    $query->where('sections.section_id', $registrationData['section_id']);
                })
                ->get();

            if ($advisers->isEmpty()) {
                \Log::warning("No advisers found for section {$registrationData['section_id']}");
                return;
            }

            // Create notification for each adviser
            foreach ($advisers as $adviser) {
                \App\Models\Notification::create([
                    'user_id' => $adviser->id,
                    'type' => 'student_registration_pending',
                    'title' => 'New Student Registration',
                    'message' => "A new student {$registrationData['first_name']} {$registrationData['last_name']} ({$registrationData['username']}) has registered and is waiting for approval in section {$section->section_name}.",
                    'data' => [
                        'user_id' => $user->id,
                        'student_number' => $registrationData['username'],
                        'student_name' => "{$registrationData['first_name']} {$registrationData['last_name']}",
                        'section_id' => $registrationData['section_id'],
                        'section_name' => $section->section_name,
                        'registration_date' => now()->format('M d, Y \a\t g:i A'),
                        'status' => 'pending_approval',
                        'redirect_url' => route('student-verification'),
                    ],
                    'is_read' => false,
                ]);
            }

            \Log::info("Created pending approval notifications for " . $advisers->count() . " advisers for student {$user->id}");
        } catch (\Exception $e) {
            \Log::error("Failed to create pending approval notification: " . $e->getMessage());
        }
    }
}
