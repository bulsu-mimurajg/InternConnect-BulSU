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

            // Check if user already exists (in case of race condition)
            $existingUser = User::where('email', $registrationData['email'])->first();
            if ($existingUser) {
                DB::rollBack();
                Cache::forget("registration_verification_{$token}");
                return redirect()->route('login')->withErrors([
                    'verification' => 'This email is already registered.'
                ]);
            }

            // Create the user account
            $user = User::create([
                'username' => $registrationData['username'],
                'email' => $registrationData['email'],
                'password' => $registrationData['password'],
                'status' => 'unverified', // User is created but needs adviser verification
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

            DB::commit();

            // Remove the cached registration data
            Cache::forget("registration_verification_{$token}");

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
}