<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\AcademeAccount;
use App\Models\StudentScore;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AdviserController extends Controller
{
    /**
     * Display the adviser dashboard with comprehensive statistics.
     */
    public function dashboard(): Response
    {
        $adviser = Auth::user();
        
        // Get the adviser's section from advisers table
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            return Inertia::render('adviser/dashboard', [
                'stats' => [],
                'recentAssessments' => [],
                'placementOverview' => [],
                'adviserSection' => null,
            ]);
        }

        $sectionId = $adviserRecord->section_id;

        // Get comprehensive statistics
        $stats = $this->getDashboardStats($sectionId);
        
        // Get recent assessment submissions
        $recentAssessments = $this->getRecentAssessments($sectionId);
        
        // Get placement overview
        $placementOverview = $this->getPlacementOverview($sectionId);

        return Inertia::render('adviser/dashboard', [
            'stats' => $stats,
            'recentAssessments' => $recentAssessments,
            'placementOverview' => $placementOverview,
            'adviserSection' => $adviserRecord->section->section_name ?? null,
        ]);
    }

    /**
     * Get dashboard statistics for the adviser's section.
     */
    private function getDashboardStats($sectionId): array
    {
        // Total students in section
        $totalStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', '!=', 'archived')
            ->count();

        // Students who have completed assessment
        $completedAssessments = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })
            ->where('status', '!=', 'archived')
            ->count();

        // Students who have been placed (placeholder for now)
        $placedStudents = 0; // Will be implemented when student matches are added

        // Pending students (not yet verified)
        $pendingStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereDoesntHave('student')
            ->where('status', '!=', 'archived')
            ->count();

        // Average assessment score for the section
        $averageScore = StudentScore::whereHas('student.user.academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->avg('score') ?? 0;

        return [
            'totalStudents' => $totalStudents,
            'completedAssessments' => $completedAssessments,
            'placedStudents' => $placedStudents,
            'pendingStudents' => $pendingStudents,
            'averageScore' => round($averageScore, 2),
            'completionRate' => $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) : 0,
            'placementRate' => $completedAssessments > 0 ? round(($placedStudents / $completedAssessments) * 100, 1) : 0,
        ];
    }

    /**
     * Get recent assessment submissions for the adviser's section.
     */
    private function getRecentAssessments($sectionId): array
    {
        return User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })
            ->where('status', '!=', 'archived')
            ->with(['student.scores.subcategory.category'])
            ->get()
            ->map(function ($user) {
                $student = $user->student;
                $totalScore = $student->scores->sum('score');
                $maxPossibleScore = $student->scores->count() * 5; // Assuming 5 is max score per question
                $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 1) : 0;
                
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'totalScore' => $totalScore,
                    'percentage' => $percentage,
                    'submittedAt' => $student->updated_at->format('M d, Y'),
                    'categories' => $student->scores->groupBy('subcategory.category.category_name')
                        ->map(function ($scores, $categoryName) {
                            return [
                                'name' => $categoryName,
                                'score' => round($scores->avg('score'), 2),
                            ];
                        })->values()->toArray(),
                ];
            })
            ->sortByDesc('submittedAt')
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Get placement overview for the adviser's section.
     */
    private function getPlacementOverview($sectionId): array
    {
        // Placeholder for placement data - will be implemented when student matches are added
        return [
            'studentsWithPlacements' => [],
            'placementsByCompany' => [],
            'totalPlaced' => 0,
            'totalUnplaced' => 0,
        ];
    }

    /**
     * Display the adviser application page with pending students.
     */
    public function index(): Response
    {
        $adviser = Auth::user();
        
        // Get the adviser's section from advisers table
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            return Inertia::render('adviser/application', [
                'pendingStudents' => [],
                'verifiedStudents' => [],
                'adviserSection' => null,
            ]);
        }

        $sectionId = $adviserRecord->section_id;

        // Get pending students (users with student role in the same section who don't have a student record)
        $pendingStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereDoesntHave('student')
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'section' => [
                        'section_id' => $user->academeAccounts->first()->section->section_id,
                        'section_name' => $user->academeAccounts->first()->section->section_name,
                    ],
                ];
            });

        // Get verified students (users with student role in the same section who have a student record)
        $verifiedStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereHas('student')
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section', 'student'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'section' => [
                        'section_id' => $user->academeAccounts->first()->section->section_id,
                        'section_name' => $user->academeAccounts->first()->section->section_name,
                    ],
                ];
            });

        return Inertia::render('adviser/application', [
            'pendingStudents' => $pendingStudents,
            'verifiedStudents' => $verifiedStudents,
            'adviserSection' => $adviserRecord->section->section_name ?? null,
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
                $userSection = $user->academeAccounts()->first()->section;

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
                    'section_id' => $userSection->section_id,
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
                
                // Remove student role
                $user->removeRole('student');
                
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
                        
                        $userSection = $user->academeAccounts()->first()->section;
                        Student::create([
                            'user_id' => $user->id,
                            'student_number' => $user->username,
                            'first_name' => 'Pending',
                            'last_name' => 'Student',
                            'middle_name' => '',
                            'phone' => '',
                            'section_id' => $userSection->section_id,
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

    /**
     * Get detailed student information for the adviser's section.
     */
    public function getStudents(): Response
    {
        $adviser = Auth::user();
        
        // Get the adviser's section from advisers table
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            return Inertia::render('adviser/students', [
                'students' => [],
                'adviserSection' => null,
            ]);
        }

        $sectionId = $adviserRecord->section_id;

        // Get all students in the section with their details
        $students = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', '!=', 'archived')
            ->with([
                'academeAccounts.section',
                'student.scores.subcategory.category'
            ])
            ->get()
            ->map(function ($user) {
                $student = $user->student;
                $hasAssessment = $student && $student->is_submit;
                
                if ($hasAssessment) {
                    $totalScore = $student->scores->sum('score');
                    $maxPossibleScore = $student->scores->count() * 5;
                    $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 1) : 0;
                    
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'section' => $user->academeAccounts->first()->section->section_name ?? '',
                        'status' => $user->status,
                        'hasAssessment' => true,
                        'assessmentScore' => $totalScore,
                        'assessmentPercentage' => $percentage,
                        'assessmentSubmittedAt' => $student->updated_at->format('M d, Y'),
                        'isPlaced' => $student->is_placed || $student->placements()->where('status', 'approved')->exists(),
                        'placement' => null,
                        'categories' => $student->scores->groupBy('subcategory.category.category_name')
                            ->map(function ($scores, $categoryName) {
                                return [
                                    'name' => $categoryName,
                                    'score' => round($scores->avg('score'), 2),
                                ];
                            })->values()->toArray(),
                    ];
                } else {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'name' => $student ? ($student->first_name . ' ' . $student->last_name) : 'Pending',
                        'section' => $user->academeAccounts->first()->section->section_name ?? '',
                        'status' => $user->status,
                        'hasAssessment' => false,
                        'assessmentScore' => 0,
                        'assessmentPercentage' => 0,
                        'assessmentSubmittedAt' => null,
                        'isPlaced' => $student ? ($student->is_placed || $student->placements()->where('status', 'approved')->exists()) : false,
                        'placement' => null,
                        'categories' => [],
                    ];
                }
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        return Inertia::render('adviser/students', [
            'students' => $students,
            'adviserSection' => $adviserRecord->section->section_name ?? null,
        ]);
    }


}
