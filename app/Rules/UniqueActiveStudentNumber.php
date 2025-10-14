<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Student;
use App\Models\User;

class UniqueActiveStudentNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if student number is already in use by an active student
        $existingStudent = Student::where('student_number', $value)
            ->where('is_active', true)
            ->first();

        if ($existingStudent) {
            $fail("The student number '{$value}' is already in use by an active student.");
            return;
        }

        // Check if username exists with a non-archived user
        // This prevents multiple active users with same username
        $existingUser = User::where('username', $value)
            ->whereIn('status', ['verified', 'unverified']) // Only block if user is not archived
            ->first();
        
        if ($existingUser) {
            $fail("The student number '{$value}' is already registered with an active account.");
        }
        
        // If only archived users exist with this username, registration is allowed
    }
}