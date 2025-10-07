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

        // Get all sections assigned to this adviser (only active sections)
        $adviserSections = $adviserRecord->sections()->where('status', 'active')->get();

        if ($adviserSections->isEmpty()) {
            // Check if adviser has any sections (including archived ones)
            $allAdviserSections = $adviserRecord->sections;
            $archivedSections = $allAdviserSections->where('status', 'archived');
            
            if ($archivedSections->isNotEmpty()) {
                return Inertia::render('adviser/dashboard', [
                    'stats' => [],
                    'recentAssessments' => [],
                    'placementOverview' => [],
                    'adviserSection' => null,
                    'adviserSections' => [],
                    'currentSectionId' => null,
                    'hasArchivedSections' => true,
                    'archivedSectionNames' => $archivedSections->pluck('section_name')->toArray(),
                ]);
            }
            
            return Inertia::render('adviser/dashboard', [
                'stats' => [],
                'recentAssessments' => [],
                'placementOverview' => [],
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
                'hasArchivedSections' => false,
                'archivedSectionNames' => [],
            ]);
        }

        // Get current section from session or default to first section
        $currentSectionId = $this->getCurrentSectionId($request, $adviserSections);
        $currentSection = $currentSectionId ? $adviserSections->where('section_id', $currentSectionId)->first() : null;

        // Get comprehensive statistics
        $stats = $this->getDashboardStats($currentSectionId, $adviserSections);

        // Get recent assessment submissions
        $recentAssessments = $this->getRecentAssessments($currentSectionId, $adviserSections);

        // Get placement overview
        $placementOverview = $this->getPlacementOverview($currentSectionId, $adviserSections);

        return Inertia::render('adviser/dashboard', [
            'stats' => $stats,
            'recentAssessments' => $recentAssessments,
            'placementOverview' => $placementOverview,
            'adviserSection' => $currentSection ? $currentSection->section_name : ($adviserSections->count() > 1 ? 'All Sections' : $adviserSections->first()->section_name),
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
    private function getDashboardStats($sectionId, $adviserSections): array
    {
        // If sectionId is null, aggregate data from all sections
        if ($sectionId === null) {
            $sectionIds = $adviserSections->pluck('section_id')->toArray();

            // Total students across all sections
            $totalStudents = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                    $query->whereIn('section_id', $sectionIds);
                })
                ->where('status', '!=', 'archived')
                ->count();

            // Students who have completed assessment across all sections
            $completedAssessments = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                    $query->whereIn('section_id', $sectionIds);
                })
                ->whereHas('student', function ($query) {
                    $query->where('is_submit', true);
                })
                ->where('status', '!=', 'archived')
                ->count();

            // Students who have been placed across all sections
            $placedStudents = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                    $query->whereIn('section_id', $sectionIds);
                })
                ->whereHas('student.placements', function ($query) {
                    $query->where('status', 'approved');
                })
                ->where('status', '!=', 'archived')
                ->count();

            // Pending students across all sections
            $pendingStudents = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                    $query->whereIn('section_id', $sectionIds);
                })
                ->whereDoesntHave('student')
                ->where('status', '!=', 'archived')
                ->count();

            // Average assessment score across all sections
            $averageScore = StudentScore::whereHas('student.user.academeAccounts', function ($query) use ($sectionIds) {
                    $query->whereIn('section_id', $sectionIds);
                })
                ->avg('score') ?? 0;
        } else {
            // Single section logic (existing code)
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

            $placedStudents = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                    $query->where('section_id', $sectionId);
                })
                ->whereHas('student.placements', function ($query) {
                    $query->where('status', 'approved');
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
        }

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
    private function getRecentAssessments($sectionId, $adviserSections): array
    {
        $query = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })
            ->where('status', '!=', 'archived')
            ->with(['student.scores.subcategory.category', 'academeAccounts.section']);

        // If sectionId is null, get from all sections
        if ($sectionId === null) {
            $sectionIds = $adviserSections->pluck('section_id')->toArray();
            $query->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                $query->whereIn('section_id', $sectionIds);
            });
        } else {
            $query->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            });
        }

        return $query->get()
            ->map(function ($user) {
                $student = $user->student;
                $totalScore = $student->scores->sum('score');
                $scoreCount = $student->scores->count();

                // Check if scores are stored as percentages (51-100) instead of 1-5 scale
                $hasHighScores = $student->scores->where('score', '>', 10)->count() > 0;

                if ($hasHighScores) {
                    // Scores are already in percentage format, just average them
                    $percentage = $scoreCount > 0 ? round($totalScore / $scoreCount, 1) : 0;
                } else {
                    // Scores are in 1-5 scale, convert to percentage
                    $maxPossibleScore = $scoreCount * 5;
                    $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 1) : 0;
                }

                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'section' => $user->academeAccounts->first()->section->section_name ?? 'Unknown',
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
    private function getPlacementOverview($sectionId, $adviserSections): array
    {
        $query = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })
            ->where('status', '!=', 'archived')
            ->with(['student.placements.internship.hte', 'academeAccounts.section']);

        // If sectionId is null, get from all sections
        if ($sectionId === null) {
            $sectionIds = $adviserSections->pluck('section_id')->toArray();
            $query->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                $query->whereIn('section_id', $sectionIds);
            });
        } else {
            $query->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            });
        }

        $students = $query->get();

        $studentsWithPlacements = [];
        $placementsByCompany = [];
        $totalPlaced = 0;
        $totalUnplaced = 0;

        foreach ($students as $user) {
            $student = $user->student;
            $placements = $student->placements;

            // Check if student has any approved placements
            $approvedPlacements = $placements->where('status', 'approved');
            $isPlaced = $approvedPlacements->isNotEmpty();

            if ($isPlaced) {
                $totalPlaced++;

                // Get the best placement (highest compatibility score)
                $topMatch = $approvedPlacements->sortByDesc('compatibility_score')->first();

                $studentsWithPlacements[] = [
                    'id' => $student->id,
                    'username' => $user->username,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'section' => $user->academeAccounts->first()->section->section_name ?? 'Unknown',
                    'isPlaced' => true,
                    'topMatch' => [
                        'position' => $topMatch->internship->position_title ?? 'N/A',
                        'company' => $topMatch->internship->hte->company_name ?? 'N/A',
                        'compatibilityScore' => round($topMatch->compatibility_score, 1),
                        'rank' => 1, // This could be calculated based on score ranking
                    ],
                ];

                // Track placements by company
                $companyName = $topMatch->internship->hte->company_name ?? 'Unknown Company';
                if (isset($placementsByCompany[$companyName])) {
                    $placementsByCompany[$companyName]['count']++;
                    $placementsByCompany[$companyName]['students'][] = $student->first_name . ' ' . $student->last_name;
                } else {
                    $placementsByCompany[$companyName] = [
                        'company' => $companyName,
                        'count' => 1,
                        'students' => [$student->first_name . ' ' . $student->last_name],
                    ];
                }
            } else {
                $totalUnplaced++;

                $studentsWithPlacements[] = [
                    'id' => $student->id,
                    'username' => $user->username,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'section' => $user->academeAccounts->first()->section->section_name ?? 'Unknown',
                    'isPlaced' => false,
                    'topMatch' => null,
                ];
            }
        }

        return [
            'studentsWithPlacements' => $studentsWithPlacements,
            'placementsByCompany' => array_values($placementsByCompany),
            'totalPlaced' => $totalPlaced,
            'totalUnplaced' => $totalUnplaced,
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

        // Get all sections assigned to this adviser (only active sections)
        $adviserSections = $adviserRecord->sections()->where('status', 'active')->get();

        if ($adviserSections->isEmpty()) {
            // Check if adviser has any sections (including archived ones)
            $allAdviserSections = $adviserRecord->sections;
            $archivedSections = $allAdviserSections->where('status', 'archived');
            
            if ($archivedSections->isNotEmpty()) {
                return Inertia::render('adviser/application', [
                    'pendingStudents' => [],
                    'verifiedStudents' => [],
                    'adviserSection' => null,
                    'adviserSections' => [],
                    'currentSectionId' => null,
                    'hasArchivedSections' => true,
                    'archivedSectionNames' => $archivedSections->pluck('section_name')->toArray(),
                ]);
            }
            
            return Inertia::render('adviser/application', [
                'pendingStudents' => [],
                'verifiedStudents' => [],
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
                'hasArchivedSections' => false,
                'archivedSectionNames' => [],
            ]);
        }

        // Get current section from session or default to first section
        $currentSectionId = $this->getCurrentSectionId($request, $adviserSections);
        $currentSection = $currentSectionId ? $adviserSections->where('section_id', $currentSectionId)->first() : null;

        // Build query for pending students (unverified students)
        $pendingQuery = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->where('status', 'unverified')
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section', 'student']);

        // Build query for verified students
        $verifiedQuery = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->where('status', 'verified')
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section', 'student']);

        // Build query for rejected students (archived status)
        $rejectedQuery = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->where('status', 'archived')
            ->with(['academeAccounts.section', 'student']);

        // Apply section filter
        if ($currentSectionId === null) {
            // All sections
            $sectionIds = $adviserSections->pluck('section_id')->toArray();
            $pendingQuery->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                $query->whereIn('section_id', $sectionIds);
            });
            $verifiedQuery->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                $query->whereIn('section_id', $sectionIds);
            });
            $rejectedQuery->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                $query->whereIn('section_id', $sectionIds);
            });
        } else {
            // Single section
            $pendingQuery->whereHas('academeAccounts', function ($query) use ($currentSectionId) {
                $query->where('section_id', $currentSectionId);
            });
            $verifiedQuery->whereHas('academeAccounts', function ($query) use ($currentSectionId) {
                $query->where('section_id', $currentSectionId);
            });
            $rejectedQuery->whereHas('academeAccounts', function ($query) use ($currentSectionId) {
                $query->where('section_id', $currentSectionId);
            });
        }

        // Get pending students
        $pendingStudents = $pendingQuery->get()
            ->map(function ($user) {
                // Get cached registration data for pending students
                $registrationData = Cache::get("registration_data_{$user->email}");

                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'academe_accounts' => $user->academeAccounts->map(function ($account) {
                        return [
                            'section' => [
                                'section_id' => $account->section->section_id,
                                'section_name' => $account->section->section_name,
                            ]
                        ];
                    })->toArray(),
                    'student' => $user->student ? [
                        'id' => $user->student->id,
                        'student_number' => $user->student->student_number,
                        'first_name' => $user->student->first_name,
                        'last_name' => $user->student->last_name,
                        'is_submit' => $user->student->is_submit,
                    ] : null,
                    'registration_data' => $registrationData ? [
                        'first_name' => $registrationData['first_name'],
                        'last_name' => $registrationData['last_name'],
                        'middle_name' => $registrationData['middle_name'] ?? '',
                    ] : null,
                ];
            });

        // Get verified students
        $verifiedStudents = $verifiedQuery->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'academe_accounts' => $user->academeAccounts->map(function ($account) {
                        return [
                            'section' => [
                                'section_id' => $account->section->section_id,
                                'section_name' => $account->section->section_name,
                            ]
                        ];
                    })->toArray(),
                    'student' => $user->student ? [
                        'id' => $user->student->id,
                        'student_number' => $user->student->student_number,
                        'first_name' => $user->student->first_name,
                        'last_name' => $user->student->last_name,
                        'is_submit' => $user->student->is_submit,
                    ] : null,
                ];
            });

        // Get rejected students
        $rejectedStudents = $rejectedQuery->get()
            ->map(function ($user) {
                // Get cached registration data for rejected students
                $registrationData = Cache::get("registration_data_{$user->email}");

                // If no cached data, check if there's a student record (in case they were approved then rejected)
                $studentData = null;
                if (!$registrationData && $user->student) {
                    $studentData = [
                        'first_name' => $user->student->first_name,
                        'last_name' => $user->student->last_name,
                        'middle_name' => $user->student->middle_name ?? '',
                    ];
                }

                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'academe_accounts' => $user->academeAccounts->map(function ($account) {
                        return [
                            'section' => [
                                'section_id' => $account->section->section_id,
                                'section_name' => $account->section->section_name,
                            ]
                        ];
                    })->toArray(),
                    'registration_data' => $registrationData ? [
                        'first_name' => $registrationData['first_name'],
                        'last_name' => $registrationData['last_name'],
                        'middle_name' => $registrationData['middle_name'] ?? '',
                    ] : $studentData,
                    'rejected_at' => $user->updated_at->format('M d, Y'),
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
            'rejectedStudents' => $rejectedStudents,
            'adviserSection' => $currentSection ? $currentSection->section_name : ($adviserSections->count() > 1 ? 'All Sections' : $adviserSections->first()->section_name),
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
        $deadlineActive = \App\Models\Deadline::isActiveForCategory('student_verification');
        if (!$deadlineActive) {
            \Log::info('Student verification deadline check failed', [
                'deadline_active' => $deadlineActive,
                'current_time' => now(),
                'deadlines' => \App\Models\Deadline::where('category', 'student_verification')->get()->toArray()
            ]);

            // For now, let's create a deadline if none exists (temporary fix for testing)
            $deadline = \App\Models\Deadline::where('category', 'student_verification')->first();
            if (!$deadline) {
                \App\Models\Deadline::create([
                    'title' => 'Student Verification Period',
                    'category' => 'student_verification',
                    'start_date' => now()->subDay(),
                    'end_date' => now()->addDays(30),
                    'status' => 'active',
                ]);
                \Log::info('Created new student verification deadline for testing');
            } else {
                return back()->withErrors(['error' => 'Student verification deadline has expired. You cannot approve students at this time.']);
            }
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

                \Log::info('Processing user for approval', [
                    'user_id' => $userId,
                    'username' => $user->username,
                    'current_status' => $user->status,
                    'has_student_record' => $user->student ? true : false
                ]);

                // Check if user is already verified
                if ($user->status === 'verified') {
                    $errors[] = "User {$user->username} is already verified.";
                    continue;
                }

                // Ensure user has student role
                $user->assignRole('student');

                // Update status to verified
                $user->update(['status' => 'verified']);

                // Only create student record if it doesn't exist
                if (!$user->student) {
                    // Get user's section
                    $userSection = $user->academeAccounts()->first()->section;

                    // Get registration data from cache using user's email
                    $registrationData = Cache::get("registration_data_{$user->email}");

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
                    ]);

                    // Load the section relationship
                    $student->load('section');

                    // Clean up the cached registration data after creating student record
                    Cache::forget("registration_data_{$user->email}");
                }

                $approvedCount++;
                \Log::info('Successfully approved user', ['user_id' => $userId, 'username' => $user->username]);
            } catch (\Exception $e) {
                \Log::error('Error processing user for approval', [
                    'user_id' => $userId,
                    'username' => $user->username,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
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

                // Check if student has submitted their assessment
                if ($user->student && $user->student->is_submit) {
                    $errors[] = "Cannot remove access for {$user->username} - assessment already submitted";
                    continue;
                }

                // Only update status to unverified to disable login
                $user->update(['status' => 'unverified']);

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
     * Restore rejected students back to pending status.
     */
    public function restoreStudents(Request $request)
    {
        $request->validate([
            'studentIds' => 'required|array',
            'studentIds.*' => 'exists:users,id'
        ]);

        $restoredCount = 0;
        $errors = [];

        foreach ($request->studentIds as $userId) {
            try {
                $user = User::findOrFail($userId);

                // Only restore if user is archived (rejected)
                if ($user->status !== 'archived') {
                    $errors[] = "User {$user->username} is not in rejected status.";
                    continue;
                }

                // Set status back to unverified
                $user->update(['status' => 'unverified']);

                $restoredCount++;
            } catch (\Exception $e) {
                $errors[] = "Error processing user {$user->username}: " . $e->getMessage();
            }
        }

        $message = "Successfully restored {$restoredCount} student(s) to pending status.";
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
            'action' => 'required|in:approve,reject,remove,restore',
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
                        // Restore status to verified
                        $user->update(['status' => 'verified']);
                        break;

                    case 'restore':
                        // Set status back to archived
                        $user->update(['status' => 'archived']);
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

        // Get all sections assigned to this adviser (only active sections)
        $adviserSections = $adviserRecord->sections()->where('status', 'active')->get();

        if ($adviserSections->isEmpty()) {
            // Check if adviser has any sections (including archived ones)
            $allAdviserSections = $adviserRecord->sections;
            $archivedSections = $allAdviserSections->where('status', 'archived');
            
            if ($archivedSections->isNotEmpty()) {
                return Inertia::render('adviser/students', [
                    'students' => [],
                    'adviserSection' => null,
                    'adviserSections' => [],
                    'currentSectionId' => null,
                    'hasArchivedSections' => true,
                    'archivedSectionNames' => $archivedSections->pluck('section_name')->toArray(),
                ]);
            }
            
            return Inertia::render('adviser/students', [
                'students' => [],
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
                'hasArchivedSections' => false,
                'archivedSectionNames' => [],
            ]);
        }

        // Get current section from session or default to first section
        $currentSectionId = $this->getCurrentSectionId($request, $adviserSections);
        $currentSection = $currentSectionId ? $adviserSections->where('section_id', $currentSectionId)->first() : null;

        // Build query for students
        $query = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->where('status', '!=', 'archived')
            ->with([
                'academeAccounts.section',
                'student.scores.subcategory.category'
            ]);

        // Apply section filter
        if ($currentSectionId === null) {
            // All sections
            $sectionIds = $adviserSections->pluck('section_id')->toArray();
            $query->whereHas('academeAccounts', function ($query) use ($sectionIds) {
                $query->whereIn('section_id', $sectionIds);
            });
        } else {
            // Single section
            $query->whereHas('academeAccounts', function ($query) use ($currentSectionId) {
                $query->where('section_id', $currentSectionId);
            });
        }

        // Get all students with their details
        $students = $query->get()
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
            'adviserSection' => $currentSection ? $currentSection->section_name : ($adviserSections->count() > 1 ? 'All Sections' : $adviserSections->first()->section_name),
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
    private function getCurrentSectionId(Request $request, $adviserSections): int|null
    {
        // Try to get from session first
        $sessionSectionId = $request->session()->get('adviser_current_section_id');

        // Handle "All Sections" option (null value) - only if explicitly set
        if ($sessionSectionId === 'all') {
            return null;
        }

        if ($sessionSectionId && $adviserSections->contains('section_id', $sessionSectionId)) {
            return $sessionSectionId;
        }

        // Default behavior based on number of sections
        if ($adviserSections->count() === 1) {
            // Single section: default to that section
            $firstSectionId = $adviserSections->first()->section_id;
            $request->session()->put('adviser_current_section_id', $firstSectionId);
            return $firstSectionId;
        } else {
            // Multiple sections: default to "All Sections"
            $request->session()->put('adviser_current_section_id', 'all');
            return null;
        }
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

        // Handle "All Sections" option
        if ($sectionId === 'all') {
            $request->session()->put('adviser_current_section_id', 'all');
            return redirect()->back()->with('success', 'Switched to All Sections view.');
        }

        // Verify the adviser has access to this section and it's not archived
        $hasAccess = $adviserRecord->sections()
            ->where('section_id', $sectionId)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            // Check if the section exists but is archived
            $isArchived = $adviserRecord->sections()
                ->where('section_id', $sectionId)
                ->where('status', 'archived')
                ->exists();
                
            if ($isArchived) {
                return redirect()->back()->withErrors(['error' => 'Cannot switch to archived section. Please contact an administrator.']);
            }
            
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

        // Get all sections assigned to this adviser (only active sections)
        $adviserSections = $adviserRecord->sections()->where('status', 'active')->get();

        if ($adviserSections->isEmpty()) {
            // Check if adviser has any sections (including archived ones)
            $allAdviserSections = $adviserRecord->sections;
            $archivedSections = $allAdviserSections->where('status', 'archived');
            
            if ($archivedSections->isNotEmpty()) {
                return Inertia::render('adviser/report', [
                    'adviserSection' => null,
                    'adviserSections' => [],
                    'currentSectionId' => null,
                    'hasArchivedSections' => true,
                    'archivedSectionNames' => $archivedSections->pluck('section_name')->toArray(),
                ]);
            }
            
            return Inertia::render('adviser/report', [
                'adviserSection' => null,
                'adviserSections' => [],
                'currentSectionId' => null,
                'hasArchivedSections' => false,
                'archivedSectionNames' => [],
            ]);
        }

        // Get current section from session or default to first section
        $currentSectionId = $this->getCurrentSectionId($request, $adviserSections);
        $currentSection = $currentSectionId ? $adviserSections->where('section_id', $currentSectionId)->first() : null;

        return Inertia::render('adviser/report', [
            'adviserSection' => $currentSection ? $currentSection->section_name : ($adviserSections->count() > 1 ? 'All Sections' : $adviserSections->first()->section_name),
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
     * Export report to PDF based on report type
     */
    public function exportPDF(Request $request, $reportType): \Illuminate\Http\Response
    {
        $adviser = Auth::user();
        $adviserRecord = $adviser->adviser;

        if (!$adviserRecord) {
            abort(403, 'Adviser record not found.');
        }

        // Get all sections assigned to this adviser (only active sections)
        $adviserSections = $adviserRecord->sections()->where('status', 'active')->get();

        if ($adviserSections->isEmpty()) {
            abort(403, 'No active sections assigned to this adviser.');
        }

        // Get current section from session
        $currentSectionId = $request->session()->get('adviser_current_section_id');

        // Handle "All Sections" mode
        if ($currentSectionId === 'all' || $currentSectionId === null) {
            $currentSectionId = null; // Set to null for "All Sections"
            $sectionName = 'All Sections';
        } else {
            // Verify the adviser has access to this section
            $hasAccess = $adviserRecord->sections->contains('section_id', $currentSectionId);
            if (!$hasAccess) {
                abort(403, 'You do not have access to this section.');
            }
            $sectionName = \App\Models\Section::find($currentSectionId)->section_name ?? 'Unknown Section';
        }

        // Validate report type
        $validReportTypes = ['student-list', 'student-assessment', 'endorsed-students', 'placed-students', 'performance-analysis'];
        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        // Get report data based on type
        $reportData = $this->getReportDataForType($currentSectionId, $reportType, $adviserSections);

        // Generate HTML content for PDF
        $html = view("reports.adviser-{$reportType}", array_merge($reportData, [
            'sectionName' => $sectionName,
            'generatedAt' => now()->format('F d, Y \a\t h:i A'),
        ]))->render();

        // Generate PDF using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Return PDF download
        return $pdf->download("{$reportType}-report-" . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export report to Excel based on report type
     */
    public function exportExcel(Request $request, $reportType): \Illuminate\Http\Response
    {
        $adviser = Auth::user();
        $adviserRecord = $adviser->adviser;

        if (!$adviserRecord) {
            abort(403, 'Adviser record not found.');
        }

        // Get all sections assigned to this adviser (only active sections)
        $adviserSections = $adviserRecord->sections()->where('status', 'active')->get();

        if ($adviserSections->isEmpty()) {
            abort(403, 'No active sections assigned to this adviser.');
        }

        // Get current section from session
        $currentSectionId = $request->session()->get('adviser_current_section_id');

        // Handle "All Sections" mode
        if ($currentSectionId === 'all' || $currentSectionId === null) {
            $currentSectionId = null; // Set to null for "All Sections"
            $sectionName = 'All Sections';
        } else {
            // Verify the adviser has access to this section
            $hasAccess = $adviserRecord->sections->contains('section_id', $currentSectionId);
            if (!$hasAccess) {
                abort(403, 'You do not have access to this section.');
            }
            $sectionName = \App\Models\Section::find($currentSectionId)->section_name ?? 'Unknown Section';
        }

        // Validate report type
        $validReportTypes = ['student-list', 'student-assessment', 'endorsed-students', 'placed-students', 'performance-analysis'];
        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        // Get report data based on type
        $reportData = $this->getReportDataForType($currentSectionId, $reportType, $adviserSections);

        // Generate Excel file using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Generate Excel content based on report type
        $this->generateAdviserExcelContent($sheet, $reportType, $reportData, $sectionName);

        // Set headers for Excel download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = "{$reportType}-report-" . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export report to CSV based on report type
     */

    /**
     * Apply section filter to a query based on current section selection
     */
    private function applySectionFilter($query, $sectionId, $adviserSections, $relation = 'academeAccounts')
    {
        if ($sectionId === null) {
            // All sections mode
            $sectionIds = $adviserSections->pluck('section_id')->toArray();
            $query->whereHas($relation, function ($q) use ($sectionIds) {
                $q->whereIn('section_id', $sectionIds);
            });
        } else {
            // Single section mode
            $query->whereHas($relation, function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            });
        }
        return $query;
    }

    /**
     * Get overview statistics for reports.
     */
    private function getOverviewStats($sectionId, $adviserSections): array
    {
        $totalStudents = $this->applySectionFilter(
            User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })->where('status', '!=', 'archived'),
            $sectionId, $adviserSections
        )->count();

        $completedAssessments = $this->applySectionFilter(
            User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })->where('status', '!=', 'archived'),
            $sectionId, $adviserSections
        )->count();

        $pendingStudents = $this->applySectionFilter(
            User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })->whereDoesntHave('student')->where('status', '!=', 'archived'),
            $sectionId, $adviserSections
        )->count();

        $averageScore = $this->applySectionFilter(
            StudentScore::query(),
            $sectionId, $adviserSections, 'student.user.academeAccounts'
        )->avg('score') ?? 0;

        $highestScore = $this->applySectionFilter(
            StudentScore::query(),
            $sectionId, $adviserSections, 'student.user.academeAccounts'
        )->max('score') ?? 0;

        $lowestScore = $this->applySectionFilter(
            StudentScore::query(),
            $sectionId, $adviserSections, 'student.user.academeAccounts'
        )->min('score') ?? 0;

        return [
            ['label' => 'Total Students', 'value' => $totalStudents],
            ['label' => 'Completed Assessments', 'value' => $completedAssessments],
            ['label' => 'Pending Students', 'value' => $pendingStudents],
            ['label' => 'Completion Rate', 'value' => $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) . '%' : '0%'],
            ['label' => 'Average Score', 'value' => round($averageScore, 2)],
            ['label' => 'Highest Score', 'value' => round($highestScore, 2)],
            ['label' => 'Lowest Score', 'value' => round($lowestScore, 2)],
            ['label' => 'Score Range', 'value' => round($highestScore - $lowestScore, 2)],
        ];
    }

    /**
     * Get assessment analytics data.
     */
    private function getAssessmentAnalytics($sectionId, $adviserSections): array
    {
        $query = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('student', function ($query) {
                $query->where('is_submit', true);
            })
            ->where('status', '!=', 'archived')
            ->with(['student.scores']);

        // Apply section filter
        $this->applySectionFilter($query, $sectionId, $adviserSections);

        $students = $query->get();

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
    private function getCategoryBreakdown($sectionId, $adviserSections): array
    {
        $query = StudentScore::with(['subcategory.category']);

        // Apply section filter
        $this->applySectionFilter($query, $sectionId, $adviserSections, 'student.user.academeAccounts');

        $categoryScores = $query->get()
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
    private function getStudentProgress($sectionId, $adviserSections): array
    {
        $query = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->where('status', '!=', 'archived')
            ->with(['student.scores.subcategory.category', 'academeAccounts.section']);

        // Apply section filter
        $this->applySectionFilter($query, $sectionId, $adviserSections);

        return $query->get()
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
                        'section' => $user->academeAccounts->first()->section->section_name ?? 'Unknown',
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
                        'email' => $user->email,
                        'name' => $student ? ($student->first_name . ' ' . $student->last_name) : 'Pending',
                        'section' => $user->academeAccounts->first()->section->section_name ?? 'Unknown',
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
    private function getMonthlyTrends($sectionId, $adviserSections): array
    {
        // Get assessment submissions by month for the last 6 months
        $months = [];
        $submissions = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');

            $query = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->whereHas('student', function ($query) use ($date) {
                    $query->where('is_submit', true)
                          ->whereMonth('updated_at', $date->month)
                          ->whereYear('updated_at', $date->year);
                })
                ->where('status', '!=', 'archived');

            // Apply section filter
            $this->applySectionFilter($query, $sectionId, $adviserSections);

            $count = $query->count();

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
    private function getStudentListData($sectionId, $dateFilter, $adviserSections)
    {
        $query = User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->where('status', '!=', 'archived')
            ->with(['academeAccounts.section', 'student.scores.subcategory.category']);

        // Apply section filter
        $this->applySectionFilter($query, $sectionId, $adviserSections);

        if ($dateFilter) {
            $query->whereHas('student', function ($query) use ($dateFilter) {
                $query->whereBetween('updated_at', [$dateFilter['start'], $dateFilter['end']]);
            });
        }

        return $query->get()->map(function ($user) {
            $student = $user->student;

            return [
                'student_number' => $student ? $student->student_number : 'N/A',
                'email' => $user->email,
                'name' => $student ? ($student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : '')) : 'Pending',
                'status' => $user->status,
                'section' => $user->academeAccounts->first()->section->section_name ?? '',
                'registered_at' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Get report data based on report type
     */
    private function getReportDataForType($sectionId, $reportType, $adviserSections): array
    {
        switch ($reportType) {
            case 'student-list':
                return [
                    'allStudents' => $this->getStudentListData($sectionId, null, $adviserSections),
                    'stats' => $this->getStudentListStats($sectionId, $adviserSections),
                ];
            case 'student-assessment':
                return [
                    'overviewStats' => $this->getOverviewStats($sectionId, $adviserSections),
                    'studentProgress' => $this->getStudentProgress($sectionId, $adviserSections),
                ];
            case 'endorsed-students':
                return [
                    'endorsedStudents' => $this->getEndorsedStudents($sectionId, $adviserSections),
                    'endorsementStats' => $this->getAdviserEndorsementStats($sectionId, $adviserSections),
                ];
            case 'placed-students':
                return [
                    'placedStudents' => $this->getPlacedStudents($sectionId, $adviserSections),
                    'overviewStats' => $this->getOverviewStats($sectionId, $adviserSections),
                ];
            case 'performance-analysis':
                return [
                    'performanceData' => $this->getAdviserPerformanceData($sectionId, $adviserSections),
                    'overviewStats' => $this->getOverviewStats($sectionId, $adviserSections),
                ];
            default:
                return [];
        }
    }

    /**
     * Generate Excel content for adviser reports
     */
    private function generateAdviserExcelContent($sheet, $reportType, $reportData, $sectionName)
    {
        $row = 1;

        // Add header
        $sheet->setCellValue('A' . $row, ucwords(str_replace('-', ' ', $reportType)) . ' report');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row += 2;

        // Add generation info
        $sheet->setCellValue('A' . $row, 'Section: ' . $sectionName);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $row += 1;

        $sheet->setCellValue('A' . $row, 'Generated: ' . now()->format('F d, Y \a\t h:i A'));
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $row += 3;

        // Generate content based on report type
        switch ($reportType) {
            case 'student-list':
                $this->generateStudentListExcel($sheet, $reportData, $row);
                break;
            case 'student-assessment':
                $this->generateStudentAssessmentExcel($sheet, $reportData, $row);
                break;
            case 'endorsed-students':
                $this->generateEndorsedStudentsExcel($sheet, $reportData, $row);
                break;
            case 'placed-students':
                $this->generatePlacedStudentsExcel($sheet, $reportData, $row);
                break;
            case 'performance-analysis':
                $this->generatePerformanceAnalysisExcel($sheet, $reportData, $row);
                break;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Generate Student List Excel content
     */
    private function generateStudentListExcel($sheet, $reportData, $startRow)
    {
        $row = $startRow;

        // Add headers
        $headers = ['Student Number', 'Name', 'Email', 'Section', 'Status', 'Registered At'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Add data
        foreach ($reportData['allStudents'] as $student) {
            $sheet->setCellValue('A' . $row, $student['student_number']);
            $sheet->setCellValue('B' . $row, $student['name']);
            $sheet->setCellValue('C' . $row, $student['email']);
            $sheet->setCellValue('D' . $row, $student['section']);
            $sheet->setCellValue('E' . $row, ucfirst($student['status']));
            $sheet->setCellValue('F' . $row, $student['registered_at'] ?? 'N/A');
            $row++;
        }
    }

    /**
     * Generate Student Assessment Excel content
     */
    private function generateStudentAssessmentExcel($sheet, $reportData, $startRow)
    {
        $row = $startRow;

        // Add headers
        $headers = ['Name', 'Email', 'Section', 'Status', 'Has Assessment', 'Score', 'Percentage', 'Submitted At'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Add data
        foreach ($reportData['studentProgress'] as $student) {
            $sheet->setCellValue('A' . $row, $student['name']);
            $sheet->setCellValue('B' . $row, $student['email']);
            $sheet->setCellValue('C' . $row, $student['section']);
            $sheet->setCellValue('D' . $row, ucfirst($student['status']));
            $sheet->setCellValue('E' . $row, $student['hasAssessment'] ? 'Yes' : 'No');
            $sheet->setCellValue('F' . $row, $student['score'] ?? 0);
            $sheet->setCellValue('G' . $row, $student['percentage'] ?? 0);
            $sheet->setCellValue('H' . $row, $student['submittedAt'] ?? 'N/A');
            $row++;
        }
    }

    /**
     * Generate Endorsed Students Excel content
     */
    private function generateEndorsedStudentsExcel($sheet, $reportData, $startRow)
    {
        $row = $startRow;

        // Add headers
        $headers = ['Student Number', 'Name', 'Section', 'Company', 'Position', 'Department', 'Compatibility Score', 'Status', 'Endorsement Date'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Add data
        foreach ($reportData['endorsedStudents'] as $student) {
            $sheet->setCellValue('A' . $row, $student['student_number']);
            $sheet->setCellValue('B' . $row, $student['name']);
            $sheet->setCellValue('C' . $row, $student['section']);
            $sheet->setCellValue('D' . $row, $student['hte_name']);
            $sheet->setCellValue('E' . $row, $student['position_title']);
            $sheet->setCellValue('F' . $row, $student['department']);
            $sheet->setCellValue('G' . $row, $student['compatibility_score']);
            $sheet->setCellValue('H' . $row, $student['status']);
            $sheet->setCellValue('I' . $row, $student['endorsed_at']);
            $row++;
        }
    }

    /**
     * Generate Placed Students Excel content
     */
    private function generatePlacedStudentsExcel($sheet, $reportData, $startRow)
    {
        $row = $startRow;

        // Add headers
        $headers = ['Student Number', 'Name', 'Section', 'Company', 'Position', 'Department', 'Score', 'Status', 'Date'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Add data
        foreach ($reportData['placedStudents'] as $student) {
            $sheet->setCellValue('A' . $row, $student['student_number']);
            $sheet->setCellValue('B' . $row, $student['name']);
            $sheet->setCellValue('C' . $row, $student['section']);
            $sheet->setCellValue('D' . $row, $student['hte_name']);
            $sheet->setCellValue('E' . $row, $student['position_title']);
            $sheet->setCellValue('F' . $row, $student['department']);
            $sheet->setCellValue('G' . $row, $student['compatibility_score']);
            $sheet->setCellValue('H' . $row, $student['status']);
            $sheet->setCellValue('I' . $row, $student['placement_date']);
            $row++;
        }
    }

    /**
     * Generate Assessment Summary CSV
     */
    private function generateAssessmentSummaryCSV($sectionId, $csvContent): string
    {
        $overviewStats = $this->getOverviewStats($sectionId);
        $assessmentAnalytics = $this->getAssessmentAnalytics($sectionId);

        $csvContent .= "Assessment Summary\n";
        $csvContent .= "Total Students," . $overviewStats['totalStudents'] . "\n";
        $csvContent .= "Completed Assessments," . $overviewStats['completedAssessments'] . "\n";
        $csvContent .= "Completion Rate," . $overviewStats['completionRate'] . "%\n";
        $csvContent .= "Average Score," . $overviewStats['averageScore'] . "\n";
        $csvContent .= "Highest Score," . $overviewStats['highestScore'] . "\n";
        $csvContent .= "Lowest Score," . $overviewStats['lowestScore'] . "\n";
        $csvContent .= "Score Range," . $overviewStats['scoreRange'] . "\n\n";

        $csvContent .= "Score Distribution\n";
        $csvContent .= "Excellent (90-100%)," . $assessmentAnalytics['scoreDistribution']['excellent'] . "\n";
        $csvContent .= "Good (80-89%)," . $assessmentAnalytics['scoreDistribution']['good'] . "\n";
        $csvContent .= "Average (70-79%)," . $assessmentAnalytics['scoreDistribution']['average'] . "\n";
        $csvContent .= "Below Average (60-69%)," . $assessmentAnalytics['scoreDistribution']['below_average'] . "\n";
        $csvContent .= "Poor (<60%)," . $assessmentAnalytics['scoreDistribution']['poor'] . "\n\n";

        $csvContent .= "Statistical Analysis\n";
        $csvContent .= "Median Score," . $assessmentAnalytics['medianScore'] . "%\n";
        $csvContent .= "Standard Deviation," . $assessmentAnalytics['standardDeviation'] . "\n";

        return $csvContent;
    }

    /**
     * Generate Performance Analysis CSV
     */
    private function generatePerformanceAnalysisCSV($sectionId, $csvContent): string
    {
        $categoryBreakdown = $this->getCategoryBreakdown($sectionId);
        $topPerformers = array_slice($this->getStudentProgress($sectionId), 0, 10);

        $csvContent .= "Category Performance\n";
        $csvContent .= "Category,Average Score,Total Questions,Percentage\n";
        foreach ($categoryBreakdown as $category) {
            $csvContent .= $category['category'] . ",";
            $csvContent .= $category['averageScore'] . ",";
            $csvContent .= $category['totalQuestions'] . ",";
            $csvContent .= $category['percentage'] . "%\n";
        }
        $csvContent .= "\n";

        $csvContent .= "Top Performers\n";
        $csvContent .= "Rank,Name,Username,Score,Percentage,Submitted At\n";
        foreach ($topPerformers as $student) {
            if ($student['hasAssessment']) {
                $csvContent .= $student['rank'] . ",";
                $csvContent .= '"' . $student['name'] . '",';
                $csvContent .= $student['username'] . ",";
                $csvContent .= $student['score'] . ",";
                $csvContent .= $student['percentage'] . ",";
                $csvContent .= ($student['submittedAt'] ?? 'N/A') . "\n";
            }
        }

        return $csvContent;
    }

    /**
     * Generate Progress report CSV
     */
    private function generateProgressReportCSV($sectionId, $csvContent): string
    {
        $studentProgress = $this->getStudentProgress($sectionId);
        $overviewStats = $this->getOverviewStats($sectionId);

        $csvContent .= "Progress Overview\n";
        $csvContent .= "Total Students," . $overviewStats['totalStudents'] . "\n";
        $csvContent .= "Completed Assessments," . $overviewStats['completedAssessments'] . "\n";
        $csvContent .= "Pending Students," . $overviewStats['pendingStudents'] . "\n";
        $csvContent .= "Completion Rate," . $overviewStats['completionRate'] . "%\n\n";

        $csvContent .= "Student Progress\n";
        $csvContent .= "Rank,Name,Username,Status,Has Assessment,Score,Percentage,Submitted At\n";
        foreach ($studentProgress as $student) {
            $csvContent .= $student['rank'] . ",";
            $csvContent .= '"' . $student['name'] . '",';
            $csvContent .= $student['username'] . ",";
            $csvContent .= $student['status'] . ",";
            $csvContent .= ($student['hasAssessment'] ? 'Yes' : 'No') . ",";
            $csvContent .= ($student['hasAssessment'] ? $student['score'] : 'N/A') . ",";
            $csvContent .= ($student['hasAssessment'] ? $student['percentage'] : 'N/A') . ",";
            $csvContent .= ($student['submittedAt'] ?? 'N/A') . "\n";
        }

        return $csvContent;
    }

    /**
     * Get endorsed students for the adviser's section
     */
    private function getEndorsedStudents($sectionId, $adviserSections): array
    {
        $query = \App\Models\Endorsement::with(['student.user', 'internship.hte']);

        // Apply section filter
        $this->applySectionFilter($query, $sectionId, $adviserSections, 'student.user.academeAccounts');

        return $query->get()
            ->map(function ($endorsement) {
                $student = $endorsement->student;
                $internship = $endorsement->internship;

                return [
                    'student_number' => $student->student_number,
                    'name' => $student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : ''),
                    'section' => $student->section->section_name ?? 'Unknown',
                    'hte_name' => $internship->hte->company_name ?? 'Unknown',
                    'position_title' => $internship->position_title ?? 'N/A',
                    'department' => $internship->department ?? 'N/A',
                    'compatibility_score' => $endorsement->compatibility_score ?? 'N/A',
                    'status' => $endorsement->status ?? 'endorsed',
                    'endorsed_at' => $endorsement->endorsement_date ? $endorsement->endorsement_date->format('Y-m-d') : ($endorsement->created_at ? $endorsement->created_at->format('Y-m-d') : 'N/A'),
                ];
            })
            ->sortByDesc('endorsed_at')
            ->values()
            ->toArray();
    }

    /**
     * Get endorsement statistics for the adviser's section
     */
    private function getAdviserEndorsementStats($sectionId, $adviserSections): array
    {
        $query = \App\Models\Endorsement::query();

        // Apply section filter
        $this->applySectionFilter($query, $sectionId, $adviserSections, 'student.user.academeAccounts');

        // Total endorsements for this section (excluding placed students)
        $totalEndorsements = (clone $query)
            ->whereDoesntHave('student.placements', function ($query) {
                $query->where('status', 'approved');
            })
            ->count();

        // Pending endorsements (not yet placed)
        $pendingEndorsements = (clone $query)
            ->whereDoesntHave('student.placements', function ($query) {
                $query->where('status', 'approved');
            })
            ->where('status', 'pending')
            ->count();

        // Approved endorsements (not yet placed)
        $approvedEndorsements = (clone $query)
            ->whereDoesntHave('student.placements', function ($query) {
                $query->where('status', 'approved');
            })
            ->where('status', 'approved')
            ->count();

        // Rejected endorsements
        $rejectedEndorsements = (clone $query)
            ->where('status', 'rejected')
            ->count();

        // Average compatibility score
        $avgCompatibilityScore = (clone $query)
            ->whereDoesntHave('student.placements', function ($query) {
                $query->where('status', 'approved');
            })
            ->whereNotNull('compatibility_score')
            ->avg('compatibility_score') ?? 0;

        return [
            'totalEndorsements' => $totalEndorsements,
            'pendingEndorsements' => $pendingEndorsements,
            'approvedEndorsements' => $approvedEndorsements,
            'rejectedEndorsements' => $rejectedEndorsements,
            'averageCompatibilityScore' => round($avgCompatibilityScore, 2),
        ];
    }

    /**
     * Get placed students for the adviser's section
     */
    private function getPlacedStudents($sectionId, $adviserSections): array
    {
        $query = \App\Models\StudentPlacement::with(['student.user', 'internship.hte']);

        // Apply section filter
        $this->applySectionFilter($query, $sectionId, $adviserSections, 'student.user.academeAccounts');

        return $query->get()
            ->map(function ($placement) {
                $student = $placement->student;
                $internship = $placement->internship;

                return [
                    'student_number' => $student->student_number,
                    'name' => $student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : ''),
                    'section' => $student->section->section_name ?? 'Unknown',
                    'hte_name' => $internship->hte->company_name ?? 'Unknown',
                    'position_title' => $internship->position_title ?? 'N/A',
                    'department' => $internship->department ?? 'N/A',
                    'compatibility_score' => $placement->compatibility_score ?? 'N/A',
                    'status' => $placement->status ?? 'placed',
                    'placement_date' => $placement->placement_date ? $placement->placement_date->format('Y-m-d') : ($placement->created_at ? $placement->created_at->format('Y-m-d') : 'N/A'),
                ];
            })
            ->sortByDesc('placement_date')
            ->values()
            ->toArray();
    }

    /**
     * Generate Endorsed Students CSV
     */
    private function generateEndorsedStudentsCSV($sectionId, $csvContent): string
    {
        $endorsedStudents = $this->getEndorsedStudents($sectionId);
        $overviewStats = $this->getOverviewStats($sectionId);

        $csvContent .= "Endorsed Students Summary\n";
        $csvContent .= "Total Students," . $overviewStats['totalStudents'] . "\n";
        $csvContent .= "Endorsed Students," . count($endorsedStudents) . "\n";
        $csvContent .= "Endorsement Rate," . ($overviewStats['totalStudents'] > 0 ? round((count($endorsedStudents) / $overviewStats['totalStudents']) * 100, 1) : 0) . "%\n\n";

        $csvContent .= "Endorsed Students Details\n";
        $csvContent .= "Student Number,Name,Section,Company,Position,Department,Compatibility Score,Status,Endorsement Date,Notes\n";

        foreach ($endorsedStudents as $endorsement) {
            $csvContent .= $endorsement['student']['student_number'] . ",";
            $csvContent .= '"' . $endorsement['student']['first_name'] . ' ' . $endorsement['student']['last_name'] . '",';
            $csvContent .= $endorsement['student']['section'] . ",";
            $csvContent .= '"' . $endorsement['internship']['hte']['company_name'] . '",';
            $csvContent .= '"' . $endorsement['internship']['position_title'] . '",';
            $csvContent .= '"' . $endorsement['internship']['department'] . '",';
            $csvContent .= $endorsement['compatibility_score'] . ",";
            $csvContent .= $endorsement['status'] . ",";
            $csvContent .= ($endorsement['endorsement_date'] ? $endorsement['endorsement_date']->format('Y-m-d') : 'N/A') . ",";
            $csvContent .= '"' . ($endorsement['notes'] ?? 'N/A') . '"\n';
        }

        return $csvContent;
    }

    /**
     * Generate Placed Students CSV
     */
    private function generatePlacedStudentsCSV($sectionId, $csvContent): string
    {
        $placedStudents = $this->getPlacedStudents($sectionId);
        $overviewStats = $this->getOverviewStats($sectionId);

        $csvContent .= "Placed Students Summary\n";
        $csvContent .= "Total Students," . $overviewStats['totalStudents'] . "\n";
        $csvContent .= "Placed Students," . count($placedStudents) . "\n";
        $csvContent .= "Placement Rate," . ($overviewStats['totalStudents'] > 0 ? round((count($placedStudents) / $overviewStats['totalStudents']) * 100, 1) : 0) . "%\n\n";

        $csvContent .= "Placed Students Details\n";
        $csvContent .= "Student Number,Name,Section,Company,Position,Department,Compatibility Score,Status,Placement Date\n";

        foreach ($placedStudents as $placement) {
            $csvContent .= $placement['student']['student_number'] . ",";
            $csvContent .= '"' . $placement['student']['first_name'] . ' ' . $placement['student']['last_name'] . '",';
            $csvContent .= $placement['student']['section'] . ",";
            $csvContent .= '"' . $placement['internship']['hte']['company_name'] . '",';
            $csvContent .= '"' . $placement['internship']['position_title'] . '",';
            $csvContent .= '"' . $placement['internship']['department'] . '",';
            $csvContent .= $placement['compatibility_score'] . ",";
            $csvContent .= $placement['status'] . ",";
            $csvContent .= ($placement['placement_date'] ? $placement['placement_date']->format('Y-m-d') : 'N/A') . "\n";
        }

        return $csvContent;
    }

    /**
     * Get student list statistics for adviser sections
     */
    private function getStudentListStats($sectionId, $adviserSections): array
    {
        $stats = [];

        if ($sectionId === null) {
            // All sections
            $totalStudents = 0;
            $activeStudents = 0;
            $inactiveStudents = 0;

            foreach ($adviserSections as $section) {
                $sectionStudents = \App\Models\User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->whereHas('academeAccounts', function ($query) use ($section) {
                    $query->where('section_id', $section->section_id);
                })
                ->where('status', '!=', 'archived')
                ->get();

                $totalStudents += $sectionStudents->count();
                $activeStudents += $sectionStudents->where('status', 'active')->count();
                $inactiveStudents += $sectionStudents->where('status', 'inactive')->count();
            }

            $stats = [
                ['label' => 'Total Students', 'value' => $totalStudents],
                ['label' => 'Active Students', 'value' => $activeStudents],
                ['label' => 'Inactive Students', 'value' => $inactiveStudents],
                ['label' => 'Sections', 'value' => $adviserSections->count()],
            ];
        } else {
            // Specific section
            $students = \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })
            ->whereHas('academeAccounts', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->where('status', '!=', 'archived')
            ->get();

            $stats = [
                ['label' => 'Total Students', 'value' => $students->count()],
                ['label' => 'Active Students', 'value' => $students->where('status', 'active')->count()],
                ['label' => 'Inactive Students', 'value' => $students->where('status', 'inactive')->count()],
                ['label' => 'Assessment Submitted', 'value' => $students->whereHas('student', function($q) { $q->where('is_submit', true); })->count()],
            ];
        }

        return $stats;
    }

    /**
     * Get performance data for adviser sections
     */
    private function getAdviserPerformanceData($sectionId, $adviserSections): array
    {
        $query = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })
        ->whereHas('student', function ($query) {
            $query->where('is_submit', true);
        })
        ->where('status', '!=', 'archived')
        ->with(['student.scores.subcategory.category', 'academeAccounts.section']);

        // Apply section filter
        $this->applySectionFilter($query, $sectionId, $adviserSections);

        $students = $query->get()
            ->map(function ($user) {
                $student = $user->student;
                $totalScore = $student->scores->sum('score');
                $maxPossibleScore = $student->scores->count() * 5;
                $percentage = $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 1) : 0;

                return [
                    'name' => $student->last_name . ', ' . $student->first_name . ($student->middle_name ? ' ' . $student->middle_name : ''),
                    'student_number' => $student->student_number,
                    'section' => $user->academeAccounts->first()->section->section_name ?? 'Unknown',
                    'score' => $totalScore,
                    'percentage' => $percentage,
                    'submittedAt' => $student->updated_at->format('Y-m-d'),
                ];
            })
            ->sortByDesc('percentage')
            ->values()
            ->toArray();

        return [
            'topPerformers' => array_slice($students, 0, 10),
            'allStudents' => $students,
        ];
    }

    /**
     * Generate Performance Analysis Excel content
     */
    private function generatePerformanceAnalysisExcel($sheet, $reportData, $startRow)
    {
        $row = $startRow;

        // Add headers for top performers
        $headers = ['Rank', 'Student Name', 'Student Number', 'Section', 'Score', 'Percentage', 'Submitted At'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Add top performers data
        $rank = 1;
        foreach ($reportData['performanceData']['topPerformers'] as $student) {
            $sheet->setCellValue('A' . $row, $rank);
            $sheet->setCellValue('B' . $row, $student['name']);
            $sheet->setCellValue('C' . $row, $student['student_number']);
            $sheet->setCellValue('D' . $row, $student['section']);
            $sheet->setCellValue('E' . $row, $student['score']);
            $sheet->setCellValue('F' . $row, $student['percentage']);
            $sheet->setCellValue('G' . $row, $student['submittedAt']);
            $row++;
            $rank++;
        }
    }


}
