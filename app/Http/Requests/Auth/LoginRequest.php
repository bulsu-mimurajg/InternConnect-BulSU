<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // First, find the user by username to check status before attempting authentication
        $user = \App\Models\User::where('username', $this->username)->first();
        
        if ($user) {
            // Check user status before authentication to prevent login events for invalid users
            if ($user->status !== 'verified') {
                RateLimiter::hit($this->throttleKey(), 3600);

                $message = $user->status === 'archived'
                    ? 'Kindly contact the administrator for further assistance.'
                    : 'Your account is not yet verified. Please contact your adviser.';

                throw ValidationException::withMessages([
                    'username' => $message,
                ]);
            }

            // Check if student's section is archived before authentication
            if ($user->hasRole('student')) {
                $academeAccount = $user->academeAccounts()->with('section')->first();
                if ($academeAccount && $academeAccount->section && $academeAccount->section->status === 'archived') {
                    RateLimiter::hit($this->throttleKey(), 3600);

                    throw ValidationException::withMessages([
                        'username' => 'Unable to login, section is archived. Please contact your administrator.',
                    ]);
                }
            }
        }

        // Now attempt authentication with credentials
        if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 3600);

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5, 3600)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}
