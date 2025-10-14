<!-- e1bd43a9-f87f-45fe-9afb-0eab83a3994a a8586de8-9b71-4bfd-b173-95ae41413c9c -->
# Fix Registration for Returning Students - Fresh Start Each Season

## Business Requirement

Students can participate in internships across multiple seasons with a **completely fresh start**:

- **New User record** created for each season (same username allowed)
- **New Student record** created for each season (same student_number allowed)
- Old records remain archived in database for historical purposes
- Only one non-archived User and one active Student with the same username/student_number at a time

**Example:**

- Season 1: User#1 (username: "2022100488", status: "verified") + Student#1 (student_number: "2022100488", is_active: true)
- End Season 1: User#1 (status: "archived") + Student#1 (is_active: false)
- Season 2: **User#2** (username: "2022100488", status: "verified") + **Student#2** (student_number: "2022100488", is_active: true)

## Current Problem

- `users.username` has UNIQUE constraint (prevents multiple User records with same username)
- Registration validation passes but email verification fails on User creation
- Error: "Failed to complete registration. Please try again or contact support."

## Solution Overview

1. **Remove UNIQUE constraint** on `users.username` via migration
2. **Update validation** to allow archived users to be "replaced" with new users
3. **Add application-level uniqueness** checks for non-archived users only
4. **Update email verification** to create new User records for returning students

## Files to Modify

### 1. Create Migration: `database/migrations/YYYY_MM_DD_HHMMSS_remove_unique_constraint_from_users_username.php`

**Purpose**: Remove unique constraint to allow multiple users with same username (when archived)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the unique constraint on username
            $table->dropUnique(['username']);
            
            // Add a regular index for performance (non-unique)
            $table->index('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the non-unique index
            $table->dropIndex(['username']);
            
            // Re-add the unique constraint
            $table->unique('username');
        });
    }
};
```

### 2. `app/Rules/UniqueActiveStudentNumber.php`

**Purpose**: Allow registration if no active student AND no non-archived user exists with that username

**Current code (lines 14-24):**

```php
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
```

**Updated code:**

```php
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
    $existingUser = \App\Models\User::where('username', $value)
        ->whereIn('status', ['verified', 'unverified']) // Only block if user is not archived
        ->first();
    
    if ($existingUser) {
        $fail("The student number '{$value}' is already registered with an active account.");
    }
    
    // If only archived users exist with this username, registration is allowed
}
```

**Add import at top:**

```php
use App\Models\User;
```

### 3. `app/Http/Controllers/Auth/EmailVerificationController.php`

**Purpose**: Always create a NEW User record (even if archived ones exist)

**Current code (lines 78-95):**

```php
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
    'email_verified_at' => now(),
    'status' => 'unverified',
]);
```

**Updated code:**

```php
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
```

### 4. `app/Http/Controllers/AdviserController.php`

**Update Student creation check (lines 676-708):**

**Current code:**

```php
if (!$user->student) {
    // Create student record...
}
```

**Updated code:**

```php
// Check if user already has an active student record
// (Should not exist for new registrations, but check to be safe)
$existingActiveStudent = Student::where('user_id', $user->id)
    ->where('is_active', true)
    ->first();

if (!$existingActiveStudent) {
    // Get user's section
    $userSection = $user->academeAccounts()->first()->section;

    // Get registration data from cache using user's email
    $registrationData = Cache::get("registration_data_{$user->email}");

    // Get active season
    $activeSeason = $this->seasonService->getActiveSeason();

    // Create student record with registration data
    $student = Student::create([
        'user_id' => $user->id,
        'student_number' => $user->username,
        'first_name' => $registrationData ? $registrationData['first_name'] : 'Pending',
        'last_name' => $registrationData ? $registrationData['last_name'] : 'Student',
        'middle_name' => $registrationData ? $registrationData['middle_name'] : '',
        'phone' => $registrationData ? $registrationData['contact_number'] : '',
        'section_id' => $userSection->section_id,
        'specialization' => $registrationData ? $registrationData['specialization'] : '',
        'is_active' => true,
        'is_submit' => false,
        'is_placed' => false,
        'internship_season_id' => $activeSeason?->id,
    ]);

    // Load the section relationship
    $student->load('section');

    // Clean up the cached registration data after creating student record
    Cache::forget("registration_data_{$user->email}");
}
```

### 5. Add Application-Level Uniqueness Helper (Optional but Recommended)

Create `app/Services/UserUniquenessService.php`:

```php
<?php

namespace App\Services;

use App\Models\User;

class UserUniquenessService
{
    /**
     * Check if username is available (no active user has it)
     */
    public static function isUsernameAvailable(string $username): bool
    {
        return !User::where('username', $username)
            ->whereIn('status', ['verified', 'unverified'])
            ->exists();
    }
    
    /**
     * Check if email is available (no active user has it)
     */
    public static function isEmailAvailable(string $email): bool
    {
        return !User::where('email', $email)
            ->whereIn('status', ['verified', 'unverified'])
            ->exists();
    }
    
    /**
     * Get active user by username (excluding archived)
     */
    public static function getActiveUserByUsername(string $username): ?User
    {
        return User::where('username', $username)
            ->whereIn('status', ['verified', 'unverified'])
            ->first();
    }
}
```

## Testing Scenarios

### Test 1: New Student Registration

- **Setup**: Username "2024999999" never used before
- **Expected**: Creates User + Student successfully
- **Verify**: Registration and email verification complete without errors

### Test 2: Returning Student (Archived from Previous Season)

- **Setup**: 
                                                                                                                                - Season 1: User#1 (username: "2022100488", status: "archived") + Student#1 (is_active: false)
                                                                                                                                - Season 2: Same person registers with username "2022100488"
- **Expected**: Creates NEW User#2 + NEW Student#2
- **Verify**: 
                                                                                                                                - Database has 2 User records with username "2022100488" (one archived, one active)
                                                                                                                                - Database has 2 Student records with student_number "2022100488" (one inactive, one active)

### Test 3: Active Student Tries to Re-register

- **Setup**: Student with active account tries to register again
- **Expected**: Validation fails at registration step
- **Error**: "The student number is already in use by an active student."

### Test 4: Different Student Same Username (Edge Case)

- **Setup**: Try to register while another user has that username active
- **Expected**: Validation fails
- **Error**: "The student number is already registered with an active account."

## Current Stuck User - Resolution Steps

**For your current registration that's stuck:**

1. **Check current state:**
```sql
SELECT id, username, email, status, created_at 
FROM users 
WHERE username = 'YOUR_STUDENT_NUMBER';
```

2. **After applying migration, clear cache:**
```php
// Run in tinker: php artisan tinker
Cache::forget('registration_verification_YOUR_TOKEN');
Cache::forget('registration_data_YOUR_EMAIL');
```

3. **User should re-register:**

- The new system will create a fresh User record
- Email verification will succeed

## Migration Steps

1. **Run the migration:**
```bash
php artisan make:migration remove_unique_constraint_from_users_username
# Add the migration code from section 1 above
php artisan migrate
```

2. **Verify migration:**
```sql
SHOW INDEX FROM users WHERE Column_name = 'username';
-- Should show only non-unique index, not unique constraint
```


## Important Notes

- **Data Integrity**: Multiple User records with same username are allowed, but only one can be non-archived
- **Historical Records**: All previous User and Student records are preserved
- **Login**: Users log in with email + password (username is for student number identification only)
- **Backwards Compatibility**: Existing code continues to work since we're adding records, not modifying existing ones

### To-dos

- [ ] Update UniqueActiveStudentNumber rule to check both students and users tables
- [ ] Add User model import to UniqueActiveStudentNumber rule
- [ ] Test registration with archived, active, and new usernames
- [ ] Provide admin instructions to clean up the current stuck user registration