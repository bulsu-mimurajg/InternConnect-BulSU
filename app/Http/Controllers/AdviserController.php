<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\AcademeAccount;
use App\Models\StudentScore;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AdviserController extends Controller
{
    /**
     * Display the adviser dashboard with comprehensive statistics.
     */
    public function dashboard(Request $request): Response
    {
        $adviser = Auth::user();
        
        // Get the adviser's sections from advisers table
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            return Inertia::render('adviser/dashboard', [
                'stats' => [],
                'recentAssessments' => [],
                'placementOverview' => [],
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
            ]);
        }

        // Get all sections assigned to this adviser
        $adviserSections = $adviserRecord->sections;
        
        if ($adviserSections->isEmpty()) {
            return Inertia::render('adviser/dashboard', [
                'stats' => [],
                'recentAssessments' => [],
                'placementOverview' => [],
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
            ]);
        }

        // Get current section from session or default to first section
        $currentSectionId = $this->getCurrentSectionId($request, $adviserSections);
        $currentSection = $adviserSections->where('section_id', $currentSectionId)->first();

        // Get comprehensive statistics
        $stats = $this->getDashboardStats($currentSectionId);
        
        // Get recent assessment submissions
        $recentAssessments = $this->getRecentAssessments($currentSectionId);
        
        // Get placement overview
        $placementOverview = $this->getPlacementOverview($currentSectionId);

        return Inertia::render('adviser/dashboard', [
            'stats' => $stats,
            'recentAssessments' => $recentAssessments,
            'placementOverview' => $placementOverview,
            'adviserSection' => $currentSection->section_name ?? null,
            'adviserSections' => $adviserSections->map(function ($section) {
                return [
                    'section_id' => $section->section_id,
                    'section_name' => $section->section_name,
                ];
            }),
            'currentSectionId' => $currentSectionId,
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
    public function index(Request $request): Response
    {
        $adviser = Auth::user();
        
        // Get the adviser's sections from advisers table
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            return Inertia::render('adviser/application', [
                'pendingStudents' => [],
                'verifiedStudents' => [],
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
            ]);
        }

        // Get all sections assigned to this adviser
        $adviserSections = $adviserRecord->sections;
        
        if ($adviserSections->isEmpty()) {
            return Inertia::render('adviser/application', [
                'pendingStudents' => [],
                'verifiedStudents' => [],
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
            ]);
        }

        // Get current section from session or default to first section
        $currentSectionId = $this->getCurrentSectionId($request, $adviserSections);
        $currentSection = $adviserSections->where('section_id', $currentSectionId)->first();

        // Get pending students (users with student role in the same section who don't have a student record)
        $pendingStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($currentSectionId) {
                $query->where('section_id', $currentSectionId);
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
            ->whereHas('academeAccounts', function ($query) use ($currentSectionId) {
                $query->where('section_id', $currentSectionId);
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

        // Check deadline status for student verification
        $deadlineActive = \App\Models\Deadline::isActiveForCategory('student_verification');
        $deadlineInfo = null;
        if (!$deadlineActive) {
            $deadlineInfo = \App\Models\Deadline::getActiveForCategory('student_verification');
        }

        return Inertia::render('adviser/application', [
            'pendingStudents' => $pendingStudents,
            'verifiedStudents' => $verifiedStudents,
            'adviserSection' => $currentSection->section_name ?? null,
            'adviserSections' => $adviserSections->map(function ($section) {
                return [
                    'section_id' => $section->section_id,
                    'section_name' => $section->section_name,
                ];
            }),
            'currentSectionId' => $currentSectionId,
            'deadlineActive' => $deadlineActive,
            'deadlineInfo' => $deadlineInfo,
        ]);
    }

    /**
     * Approve selected students by creating student records.
     */
    public function approveStudents(Request $request)
    {
        // Check if student verification deadline is active
        if (!\App\Models\Deadline::isActiveForCategory('student_verification')) {
            return back()->withErrors(['error' => 'Student verification deadline has expired. You cannot approve students at this time.']);
        }

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
                
                // Get registration data from cache using user's email
                $registrationData = Cache::get("registration_data_{$user->email}");
                
                // Create student record with registration data
                Student::create([
                    'user_id' => $user->id,
                    'student_number' => $user->username,
                    'first_name' => $registrationData ? $registrationData['first_name'] : 'Pending',
                    'last_name' => $registrationData ? $registrationData['last_name'] : 'Student',
                    'middle_name' => $registrationData ? $registrationData['middle_name'] : '',
                    'phone' => $registrationData ? $registrationData['contact_number'] : '',
                    'section_id' => $userSection->section_id,
                    'specialization' => '',
                    'address' => '',
                    'birth_date' => now()->format('Y-m-d'), // Default to today
                    'is_submit' => false,
                ]);

                // Clean up the cached registration data after creating student record
                Cache::forget("registration_data_{$user->email}");

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
    public function getStudents(Request $request): Response
    {
        $adviser = Auth::user();
        
        // Get the adviser's sections from advisers table
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            return Inertia::render('adviser/students', [
                'students' => [],
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
            ]);
        }

        // Get all sections assigned to this adviser
        $adviserSections = $adviserRecord->sections;
        
        if ($adviserSections->isEmpty()) {
            return Inertia::render('adviser/students', [
                'students' => [],
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
            ]);
        }

        // Get current section from session or default to first section
        $currentSectionId = $this->getCurrentSectionId($request, $adviserSections);
        $currentSection = $adviserSections->where('section_id', $currentSectionId)->first();

        // Get all students in the section with their details
        $students = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($currentSectionId) {
                $query->where('section_id', $currentSectionId);
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
            'adviserSection' => $currentSection->section_name ?? null,
            'adviserSections' => $adviserSections->map(function ($section) {
                return [
                    'section_id' => $section->section_id,
                    'section_name' => $section->section_name,
                ];
            }),
            'currentSectionId' => $currentSectionId,
        ]);
    }

    /**
     * Get the current section ID from session or default to first available section.
     */
    private function getCurrentSectionId(Request $request, $adviserSections): int
    {
        // Try to get from session first
        $sessionSectionId = $request->session()->get('adviser_current_section_id');
        
        if ($sessionSectionId && $adviserSections->contains('section_id', $sessionSectionId)) {
            return $sessionSectionId;
        }
        
        // Default to first section and store in session
        $firstSectionId = $adviserSections->first()->section_id;
        $request->session()->put('adviser_current_section_id', $firstSectionId);
        
        return $firstSectionId;
    }

    /**
     * Switch to a different section.
     */
    public function switchSection(Request $request, $sectionId)
    {
        $adviser = Auth::user();
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            return redirect()->back()->withErrors(['error' => 'Adviser record not found.']);
        }

        // Verify the adviser has access to this section
        $hasAccess = $adviserRecord->sections->contains('section_id', $sectionId);
        
        if (!$hasAccess) {
            return redirect()->back()->withErrors(['error' => 'You do not have access to this section.']);
        }

        // Store the selected section in session
        $request->session()->put('adviser_current_section_id', $sectionId);

        return redirect()->back()->with('success', 'Section switched successfully.');
    }

    /**
     * Display the report generation page for the adviser's section.
     */
    public function reports(Request $request): Response
    {
        $adviser = Auth::user();
        
        // Get the adviser's sections from advisers table
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            return Inertia::render('adviser/report', [
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
            ]);
        }

        // Get all sections assigned to this adviser
        $adviserSections = $adviserRecord->sections;
        
        if ($adviserSections->isEmpty()) {
            return Inertia::render('adviser/report', [
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
            ]);
        }

        // Get current section from session or default to first section
        $currentSectionId = $this->getCurrentSectionId($request, $adviserSections);
        $currentSection = $adviserSections->where('section_id', $currentSectionId)->first();

        return Inertia::render('adviser/report', [
            'adviserSection' => $currentSection->section_name ?? null,
            'adviserSections' => $adviserSections->map(function ($section) {
                return [
                    'section_id' => $section->section_id,
                    'section_name' => $section->section_name,
                ];
            }),
            'currentSectionId' => $currentSectionId,
        ]);
    }

    /**
     * Export student list report to PDF
     */
    public function exportPDF(Request $request): \Illuminate\Http\Response
    {
        $adviser = Auth::user();
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            abort(403, 'Adviser record not found.');
        }

        // Get current section from session
        $currentSectionId = $request->session()->get('adviser_current_section_id');
        if (!$currentSectionId) {
            $currentSectionId = $adviserRecord->sections->first()->section_id;
        }

        // Verify the adviser has access to this section
        $hasAccess = $adviserRecord->sections->contains('section_id', $currentSectionId);
        if (!$hasAccess) {
            abort(403, 'You do not have access to this section.');
        }

        // Get report data
        $overviewStats = $this->getOverviewStats($currentSectionId);
        $studentProgress = $this->getStudentProgress($currentSectionId);
        $categoryBreakdown = $this->getCategoryBreakdown($currentSectionId);
        $sectionName = \App\Models\Section::find($currentSectionId)->section_name ?? 'Unknown Section';

        // Generate HTML content for PDF
        $html = view('reports.adviser-student-list', [
            'overviewStats' => $overviewStats,
            'studentProgress' => $studentProgress,
            'categoryBreakdown' => $categoryBreakdown,
            'sectionName' => $sectionName,
            'generatedAt' => now()->format('F d, Y \a\t h:i A'),
        ])->render();

        // Generate PDF using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Return PDF download
        return $pdf->download('student-list-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export student list report to Excel (CSV format)
     */
    public function exportExcel(Request $request): \Illuminate\Http\Response
    {
        $adviser = Auth::user();
        $adviserRecord = $adviser->adviser;
        
        if (!$adviserRecord) {
            abort(403, 'Adviser record not found.');
        }

        // Get current section from session
        $currentSectionId = $request->session()->get('adviser_current_section_id');
        if (!$currentSectionId) {
            $currentSectionId = $adviserRecord->sections->first()->section_id;
        }

        // Verify the adviser has access to this section
        $hasAccess = $adviserRecord->sections->contains('section_id', $currentSectionId);
        if (!$hasAccess) {
            abort(403, 'You do not have access to this section.');
        }

        $sectionName = \App\Models\Section::find($currentSectionId)->section_name ?? 'Unknown Section';
        $studentData = $this->getStudentListData($currentSectionId, null);

        // Create CSV content
        $csvContent = "Student List Report - {$sectionName}\n";
        $csvContent .= "Generated: " . now()->format('F d, Y \a\t h:i A') . "\n\n";
        
        $csvContent .= "Student Information\n";
        $csvContent .= "Username,Email,Name,Status,Section,Has Assessment,Assessment Score,Assessment Percentage,Submitted At\n";
        
        foreach ($studentData as $student) {
            $csvContent .= $student['username'] . ",";
            $csvContent .= $student['email'] . ",";
            $csvContent .= '"' . $student['name'] . '",';
            $csvContent .= $student['status'] . ",";
            $csvContent .= $student['section'] . ",";
            $csvContent .= ($student['hasAssessment'] ? 'Yes' : 'No') . ",";
            $csvContent .= $student['assessmentScore'] . ",";
            $csvContent .= $student['assessmentPercentage'] . ",";
            $csvContent .= ($student['submittedAt'] ?? 'N/A') . "\n";
        }

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student-list-report-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Export student list report to CSV
     */
    public function exportCSV(Request $request): \Illuminate\Http\Response
    {
        return $this->exportExcel($request); // Same implementation for now
    }

    /**
     * Get overview statistics for reports.
     */
    private function getOverviewStats($sectionId): array
    {
        $totalStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', '!=', 'archived')
            ->count();

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

        $pendingStudents = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereDoesntHave('student')
            ->where('status', '!=', 'archived')
            ->count();

        $averageScore = StudentScore::whereHas('student.user.academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->avg('score') ?? 0;

        $highestScore = StudentScore::whereHas('student.user.academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->max('score') ?? 0;

        $lowestScore = StudentScore::whereHas('student.user.academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->min('score') ?? 0;

        return [
            'totalStudents' => $totalStudents,
            'completedAssessments' => $completedAssessments,
            'pendingStudents' => $pendingStudents,
            'completionRate' => $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) : 0,
            'averageScore' => round($averageScore, 2),
            'highestScore' => round($highestScore, 2),
            'lowestScore' => round($lowestScore, 2),
            'scoreRange' => round($highestScore - $lowestScore, 2),
        ];
    }

    /**
     * Get assessment analytics data.
     */
    private function getAssessmentAnalytics($sectionId): array
    {
        $students = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })
            ->where('status', '!=', 'archived')
            ->with(['student.scores'])
            ->get();

        $scoreDistribution = [
            'excellent' => 0, // 90-100%
            'good' => 0,      // 80-89%
            'average' => 0,   // 70-79%
            'below_average' => 0, // 60-69%
            'poor' => 0       // Below 60%
        ];

        $totalScores = [];
        
        foreach ($students as $user) {
            $student = $user->student;
            $totalScore = $student->scores->sum('score');
            $maxPossibleScore = $student->scores->count() * 5;
            $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 1) : 0;
            
            $totalScores[] = $percentage;
            
            if ($percentage >= 90) {
                $scoreDistribution['excellent']++;
            } elseif ($percentage >= 80) {
                $scoreDistribution['good']++;
            } elseif ($percentage >= 70) {
                $scoreDistribution['average']++;
            } elseif ($percentage >= 60) {
                $scoreDistribution['below_average']++;
            } else {
                $scoreDistribution['poor']++;
            }
        }

        return [
            'scoreDistribution' => $scoreDistribution,
            'totalScores' => $totalScores,
            'medianScore' => count($totalScores) > 0 ? round($this->array_median($totalScores), 2) : 0,
            'standardDeviation' => count($totalScores) > 1 ? round($this->array_standard_deviation($totalScores), 2) : 0,
        ];
    }

    /**
     * Get category breakdown data.
     */
    private function getCategoryBreakdown($sectionId): array
    {
        $categoryScores = StudentScore::whereHas('student.user.academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->with(['subcategory.category'])
            ->get()
            ->groupBy('subcategory.category.category_name')
            ->map(function ($scores, $categoryName) {
                return [
                    'category' => $categoryName,
                    'averageScore' => round($scores->avg('score'), 2),
                    'totalQuestions' => $scores->count(),
                    'maxPossibleScore' => $scores->count() * 5,
                    'percentage' => round(($scores->avg('score') / 5) * 100, 1),
                ];
            })
            ->values()
            ->toArray();

        return $categoryScores;
    }

    /**
     * Get student progress data.
     */
    private function getStudentProgress($sectionId): array
    {
        return User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', '!=', 'archived')
            ->with(['student.scores.subcategory.category'])
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
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'status' => $user->status,
                        'hasAssessment' => true,
                        'score' => $totalScore,
                        'percentage' => $percentage,
                        'submittedAt' => $student->updated_at->format('M d, Y'),
                        'rank' => 0, // Will be calculated on frontend
                    ];
                } else {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'name' => $student ? ($student->first_name . ' ' . $student->last_name) : 'Pending',
                        'status' => $user->status,
                        'hasAssessment' => false,
                        'score' => 0,
                        'percentage' => 0,
                        'submittedAt' => null,
                        'rank' => 0,
                    ];
                }
            })
            ->sortByDesc('percentage')
            ->values()
            ->map(function ($student, $index) {
                $student['rank'] = $index + 1;
                return $student;
            })
            ->toArray();
    }

    /**
     * Get monthly trends data.
     */
    private function getMonthlyTrends($sectionId): array
    {
        // Get assessment submissions by month for the last 6 months
        $months = [];
        $submissions = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');
            
            $count = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                    $query->where('section_id', $sectionId);
                })
                ->whereHas('student', function ($query) use ($date) {
                    $query->where('is_submit', true)
                          ->whereMonth('updated_at', $date->month)
                          ->whereYear('updated_at', $date->year);
                })
                ->where('status', '!=', 'archived')
                ->count();
            
            $months[] = $monthName;
            $submissions[] = $count;
        }

        return [
            'months' => $months,
            'submissions' => $submissions,
        ];
    }

    /**
     * Calculate median of an array.
     */
    private function array_median($array)
    {
        sort($array);
        $count = count($array);
        $middle = floor($count / 2);
        
        if ($count % 2 == 0) {
            return ($array[$middle - 1] + $array[$middle]) / 2;
        } else {
            return $array[$middle];
        }
    }

    /**
     * Calculate standard deviation of an array.
     */
    private function array_standard_deviation($array)
    {
        $count = count($array);
        if ($count <= 1) return 0;
        
        $mean = array_sum($array) / $count;
        $variance = 0;
        
        foreach ($array as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return sqrt($variance / ($count - 1));
    }


    /**
     * Get student list data for reports.
     */
    private function getStudentListData($sectionId, $dateFilter)
    {
        $query = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section', 'student.scores.subcategory.category']);

        if ($dateFilter) {
            $query->whereHas('student', function ($query) use ($dateFilter) {
                $query->whereBetween('updated_at', [$dateFilter['start'], $dateFilter['end']]);
            });
        }

        return $query->get()->map(function ($user) {
            $student = $user->student;
            $hasAssessment = $student && $student->is_submit;
            
            return [
                'username' => $user->username,
                'email' => $user->email,
                'name' => $student ? ($student->first_name . ' ' . $student->last_name) : 'Pending',
                'status' => $user->status,
                'section' => $user->academeAccounts->first()->section->section_name ?? '',
                'hasAssessment' => $hasAssessment,
                'assessmentScore' => $hasAssessment ? $student->scores->sum('score') : 0,
                'assessmentPercentage' => $hasAssessment ? round(($student->scores->sum('score') / ($student->scores->count() * 5)) * 100, 1) : 0,
                'submittedAt' => $hasAssessment ? $student->updated_at->format('Y-m-d H:i:s') : null,
            ];
        })->toArray();
    }



}
