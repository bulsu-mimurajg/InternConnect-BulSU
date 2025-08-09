<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\AcademeAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdviserController extends Controller
{
    /**
     * Display the adviser application page with pending students.
     */
    public function index(): Response
    {
        $adviser = Auth::user();
        
        // Get the adviser's section from academe_accounts
        $adviserSection = $adviser->academeAccounts()->with('section')->first();
        
        if (!$adviserSection) {
            return Inertia::render('adviser/application', [
                'pendingStudents' => [],
                'verifiedStudents' => [],
                'adviserSection' => null,
            ]);
        }

        // Get pending students (users with student role in the same section who don't have a student record)
        $pendingStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($adviserSection) {
                $query->where('section_id', $adviserSection->section_id);
            })
            ->whereDoesntHave('student')
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section'])
            ->get();

        // Get verified students (users with student role in the same section who have a student record)
        $verifiedStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($adviserSection) {
                $query->where('section_id', $adviserSection->section_id);
            })
            ->whereHas('student')
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section', 'student'])
            ->get();

        return Inertia::render('adviser/application', [
            'pendingStudents' => $pendingStudents,
            'verifiedStudents' => $verifiedStudents,
            'adviserSection' => $adviserSection->section,
        ]);
    }

    /**
     * Approve selected students by creating student records.
     */
    public function approveStudents(Request $request)
    {
        $request->validate([
            'studentIds' => 'required|array',
            'studentIds.*' => 'exists:users,id'
        ]);

        $approvedCount = 0;
        $errors = [];

        foreach ($request->studentIds as $userId) {
            try {
                $user = User::findOrFail($userId);
                
                // Check if user already has a student record
                if ($user->student) {
                    $errors[] = "User {$user->username} is already verified.";
                    continue;
                }

                // Get user's section
                $userSection = $user->academeAccounts->first()->section->section_name ?? '';

                // Ensure user has student role
                $user->assignRole('student');
                
                // Update status to verified
                $user->update(['status' => 'verified']);
                
                // Create student record with proper default values
                Student::create([
                    'user_id' => $user->id,
                    'student_number' => $user->username,
                    'first_name' => 'Pending', // Will be filled by student
                    'last_name' => 'Student', // Will be filled by student
                    'middle_name' => '',
                    'phone' => '',
                    'section' => $userSection,
                    'specialization' => '',
                    'address' => '',
                    'birth_date' => now()->format('Y-m-d'), // Default to today
                    'is_submit' => false,
                ]);

                $approvedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error processing user {$user->username}: " . $e->getMessage();
            }
        }

        $message = "Successfully approved {$approvedCount} student(s).";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Reject selected students by setting their status to archived.
     */
    public function rejectStudents(Request $request)
    {
        $request->validate([
            'studentIds' => 'required|array',
            'studentIds.*' => 'exists:users,id'
        ]);

        $rejectedCount = 0;
        $errors = [];

        foreach ($request->studentIds as $userId) {
            try {
                $user = User::findOrFail($userId);
                
                // Set status to archived
                $user->update(['status' => 'archived']);
                
                $rejectedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error processing user {$user->username}: " . $e->getMessage();
            }
        }

        $message = "Successfully rejected {$rejectedCount} student(s).";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove student access by setting their role to null and returning them to pending list.
     */
    public function removeStudentAccess(Request $request)
    {
        $request->validate([
            'studentIds' => 'required|array',
            'studentIds.*' => 'exists:users,id'
        ]);

        $removedCount = 0;
        $errors = [];

        foreach ($request->studentIds as $userId) {
            try {
                $user = User::findOrFail($userId);
                
               
                // Set status to unverified
                $user->update(['status' => 'unverified']);
                
                // Delete student record
                if ($user->student) {
                    $user->student()->delete();
                }
                
                $removedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error processing user {$user->username}: " . $e->getMessage();
            }
        }

        $message = "Successfully removed access for {$removedCount} student(s).";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Undo the last action (approve/reject/remove).
     */
    public function undoAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,remove',
            'studentIds' => 'required|array',
            'studentIds.*' => 'exists:users,id'
        ]);

        $undoneCount = 0;
        $errors = [];

        foreach ($request->studentIds as $userId) {
            try {
                $user = User::findOrFail($userId);
                
                switch ($request->action) {
                    case 'approve':
                        // Remove student record and set status back to unverified
                        if ($user->student) {
                            $user->student()->delete();
                        }
                        $user->update(['status' => 'unverified']);
                        break;
                        
                    case 'reject':
                        // Set status back to unverified
                        $user->update(['status' => 'unverified']);
                        break;
                        
                    case 'remove':
                        // Restore student role and create student record
                        $user->assignRole('student');
                        $user->update(['status' => 'verified']);
                        
                        $userSection = $user->academeAccounts->first()->section->section_name ?? '';
                        Student::create([
                            'user_id' => $user->id,
                            'student_number' => $user->username,
                            'first_name' => 'Pending',
                            'last_name' => 'Student',
                            'middle_name' => '',
                            'phone' => '',
                            'section' => $userSection,
                            'specialization' => '',
                            'address' => '',
                            'birth_date' => now()->format('Y-m-d'),
                            'is_submit' => false,
                        ]);
                        break;
                }
                
                $undoneCount++;
            } catch (\Exception $e) {
                $errors[] = "Error processing user {$user->username}: " . $e->getMessage();
            }
        }

        $message = "Successfully undone {$request->action} action for {$undoneCount} student(s).";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }
}
