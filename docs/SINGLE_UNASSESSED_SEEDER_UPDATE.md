# SingleUnassessedStudentSeeder - Unassessed Student and HTE Seeder

## Date
October 29, 2025

## Overview
Creates test accounts for both an unassessed student AND an unassessed HTE (Host Training Establishment/Company) with simple, easy-to-remember usernames. The seeder automatically deletes any existing records before creating fresh ones, making it idempotent and perfect for testing.

## Key Features

### 🔄 **Auto-Cleanup**
- Automatically deletes old student and HTE records before creating new ones
- Ensures fresh data on every run
- No conflicts with existing test accounts

### 🎯 **Simple Usernames**
- Student: `student`
- HTE: `hte`
- Easy to remember for quick testing

### ✅ **Unassessed Status**
- Both accounts have `is_submit = false`
- Perfect for testing assessment flows
- Simulates real-world incomplete assessment scenarios

## Created Accounts

### Student Account
**Login Credentials:**
- Username: `student`
- Email: `student@example.com`
- Password: `password`

**Student Details:**
- Student Number: `2025100001`
- Name: `Unassessed Test Student`
- Section: Auto-assigned (creates default if none exists)
- Specialization: `WMAD`
- Assessment Status: **Not Submitted** (`is_submit = false`)

### HTE Account
**Login Credentials:**
- Username: `hte`
- Email: `hte@example.com`
- Password: `password`

**Company Details:**
- Company Name: `Unassessed Company Inc.`
- Address: `123 Test Street, Sample City, SC 12345`
- Contact: `Test Contact` (HR Manager)
- Phone: `+63-900-000-0000`
- Assessment Status: **Not Submitted** (`is_submit = false`)

## Testing Scenarios

### 1. Student Assessment Flow
- Login as `student` / `password`
- Navigate to assessment page
- Complete or test assessment form
- Test validation and submission

### 2. HTE Assessment Flow
- Login as `hte` / `password`
- Navigate to HTE assessment/criteria page
- Complete or test HTE assessment
- Test company profile features

### 3. Incomplete Assessment Testing
- Both accounts start with `is_submit = false`
- Test dashboard views for unassessed users
- Test reminder/notification systems
- Test assessment prompts and CTAs

### 4. Quick Reset Testing
- Make changes to student or HTE data
- Run seeder again to reset to clean state
- Perfect for iterative testing workflows

## Use Cases

### Development
- Quick test account setup
- Consistent test data across team
- Easy to reset and retry tests
- No manual account management

### QA Testing
- Test incomplete assessment scenarios
- Verify notification systems
- Test user onboarding flows
- Validate assessment requirements

### Demo/Presentation
- Simple, memorable credentials
- Clean starting state
- Professional test data
- Quick account reset between demos

## Implementation Details

### Code Structure
The seeder now has two main sections:

1. **Student Creation Section**
   - Checks if student exists
   - Creates user with student role
   - Creates student record with `is_submit = false`

2. **HTE Creation Section**
   - Checks if HTE user exists
   - Creates user with HTE role
   - Creates HTE record with `is_submit = false`

### Idempotent Design
- Both sections check for existing records
- Can be run multiple times safely
- Only creates missing records
- Provides clear feedback on what was created/skipped

## Usage

### Run the Seeder
```bash
php artisan db:seed --class=SingleUnassessedStudentSeeder
```

### Expected Output (First Run)
```
Created unassessed student: 2025100001 (username: student)
Created unassessed HTE: Unassessed Company Inc. (username: hte)
SingleUnassessedStudentSeeder completed!
```

### Expected Output (Subsequent Runs)
```
Deleted old student by number: 2025100001
Deleted old student user: student
Deleted old HTE record: Unassessed Company Inc.
Deleted old HTE user: hte
Created unassessed student: 2025100001 (username: student)
Created unassessed HTE: Unassessed Company Inc. (username: hte)
SingleUnassessedStudentSeeder completed!
```

## Implementation Details

### Deletion Logic
Before creating new records, the seeder:
1. Looks for existing user with username `student`
2. Deletes associated student record if found
3. Deletes the user account
4. Also checks for student by student number and deletes if found
5. Repeats same process for HTE with username `hte`
6. Then creates fresh records

### Benefits of Auto-Cleanup
- ✅ **Idempotent**: Can run multiple times safely
- ✅ **Fresh Data**: Always creates clean test accounts
- ✅ **No Conflicts**: Removes stale data automatically
- ✅ **Fast Testing**: No manual cleanup needed

## Login Credentials (Quick Reference)

| Account | Username | Password | Email |
|---------|----------|----------|-------|
| Student | `student` | `password` | student@example.com |
| HTE | `hte` | `password` | hte@example.com |

## Use Cases

### 1. Development Testing
- Quick setup for testing assessment features
- No need to manually create test accounts
- Consistent test data across environments

### 2. QA Testing
- Test incomplete assessment scenarios
- Verify assessment reminder systems
- Test dashboard views for incomplete assessments

### 3. Demo Purposes
- Show assessment flows from start to finish
- Demonstrate system behavior with incomplete data
- Test user onboarding processes

## Files Modified
- `database/seeders/SingleUnassessedStudentSeeder.php`

## Benefits
1. **Comprehensive Testing**: Both student and HTE sides can be tested
2. **Time Saving**: No manual account creation needed
3. **Consistency**: Same test data across all environments
4. **Idempotent**: Safe to run multiple times
5. **Clear Feedback**: Informative console messages

## Notes
- Both accounts use the simple password "password" for easy testing
- The HTE is marked as `is_active = true` but `is_submit = false`
- Both accounts are linked to the same internship season
- The student is linked to a section (creates default if none exists)

