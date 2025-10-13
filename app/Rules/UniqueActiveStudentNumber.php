<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Student;

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
        }
    }
}