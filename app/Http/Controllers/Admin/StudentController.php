<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentMatch;
use App\Models\Internship;
use App\Models\StudentScore;
use App\Models\SubcategoryWeight;
use App\Models\StudentPlacement;
use App\Models\Endorsement;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\MatchingService;
use App\Services\NotificationService;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search', '');
        $sectionFilter = $request->get('section', 'all');
        $statusFilter = $request->get('status', 'all');
        // Build students query with filters
        $studentsQuery = Student::with(['section', 'user'])
            ->select([
                'id',
                'student_number',
                'first_name',
                'middle_name',
                'last_name',
                'section_id',
                'specialization',
                'is_active',
                'user_id'
            ]);

        // Apply status filter
        if ($statusFilter === 'active') {
            $studentsQuery->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $studentsQuery->where('is_active', false);
        } else {
            // For 'all' or other values, show both active and inactive
            // We'll handle this in the filtering logic below
        }

        // Apply section filter
        if ($sectionFilter && $sectionFilter !== 'all') {
            $studentsQuery->whereHas('section', function($q) use ($sectionFilter) {
                $q->where('section_name', $sectionFilter);
            });
        }

        // Apply search filter
        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        $students = $studentsQuery->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Transform the data to ensure section is a string
        $transformedStudents = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'section' => $student->section->section_name ?? '',
                'specialization' => $student->specialization,
                'is_active' => $student->is_active,
                'email' => $student->user->email ?? '',
            ];
        });

        // Get unverified users for the Show Unverified functionality
        $unverifiedUsers = User::with(['academeAccounts.section'])
            ->where('status', 'unverified')
            ->whereHas('roles', function($query) {
                $query->where('name', 'student');
            })
            ->orderBy('username')
            ->get();

        // Transform unverified users data
        $transformedUnverifiedUsers = $unverifiedUsers->map(function ($user) {
            $academeAccount = $user->academeAccounts()->first();
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'section' => $academeAccount ? $academeAccount->section->section_name ?? 'No Section' : 'No Section',
                'status' => $user->status,
                'created_at' => $user->created_at,
            ];
        });

        // Get archived students for the Show Archived functionality
        $archivedStudents = Student::with(['section', 'user'])
            ->select([
                'id',
                'student_number',
                'first_name',
                'middle_name',
                'last_name',
                'section_id',
                'specialization',
                'is_active',
                'user_id'
            ])
            ->where('is_active', false)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Transform archived students data
        $transformedArchivedStudents = $archivedStudents->map(function ($student) {
            return [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'middle_name' => $student->middle_name,
                'last_name' => $student->last_name,
                'section' => $student->section->section_name ?? '',
                'specialization' => $student->specialization,
                'is_active' => $student->is_active,
                'email' => $student->user->email ?? '',
            ];
        });

        // Get archived unverified users
        $archivedUnverifiedUsers = User::with(['academeAccounts.section'])
            ->where('status', 'archived')
            ->whereHas('roles', function($query) {
                $query->where('name', 'student');
            })
            ->orderBy('username')
            ->get();

        // Transform archived unverified users data
        $transformedArchivedUnverifiedUsers = $archivedUnverifiedUsers->map(function ($user) {
            $academeAccount = $user->academeAccounts()->first();
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'section' => $academeAccount ? $academeAccount->section->section_name ?? 'No Section' : 'No Section',
                'status' => $user->status,
                'created_at' => $user->created_at,
            ];
        });

        // Get all sections for filter options
        $sectionOptions = \App\Models\Section::select('section_name')
            ->withCount('students')
            ->orderBy('section_name')
            ->get()
            ->map(function ($section) {
                return [
                    'name' => $section->section_name,
                    'total_students' => $section->students_count
                ];
            });

        return Inertia::render('admin/student/list', [
            'students' => $transformedStudents,
            'unverifiedUsers' => $transformedUnverifiedUsers,
            'archivedStudents' => $transformedArchivedStudents,
            'archivedUnverifiedUsers' => $transformedArchivedUnverifiedUsers,
            'section_options' => $sectionOptions,
            'filters' => [
                'search' => $search,
                'section' => $sectionFilter,
                'status' => $statusFilter,
            ]
        ]);
    }

    /**
     * Display unverified student accounts
     */
    public function unverified()
    {
        $unverifiedUsers = User::with(['academeAccounts.section'])
            ->where('status', 'unverified')
            ->whereHas('roles', function($query) {
                $query->where('name', 'student');
            })
            ->orderBy('username')
            ->get();

        // Transform the data for frontend
        $transformedUnverifiedUsers = $unverifiedUsers->map(function ($user) {
            $academeAccount = $user->academeAccounts()->first();
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'section' => $academeAccount ? $academeAccount->section->section_name ?? 'No Section' : 'No Section',
                'status' => $user->status,
                'created_at' => $user->created_at,
            ];
        });

        return Inertia::render('admin/student/unverified', ['unverifiedUsers' => $transformedUnverifiedUsers]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        // Add debugging
        Log::info('Edit method called for student ID: ' . $student->id);
        Log::info('Current user: ' . Auth::user()->email ?? 'No user');
        Log::info('User roles: ' . Auth::user()->getRoleNames()->implode(', ') ?? 'No roles');
        
        $student->load(['section', 'user']);
        
        // Get all available sections for the dropdown
        $sections = \App\Models\Section::where('status', 'active')
            ->orderBy('section_name')
            ->get(['section_id', 'section_name']);
        
        $formattedStudent = [
            'id' => $student->id,
            'student_number' => $student->student_number,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'section_id' => $student->section_id,
            'section' => $student->section->section_name ?? '',
            'specialization' => $student->specialization,
            'email' => $student->user->email ?? '',
        ];
        
        return Inertia::render('admin/student/edit', [
            'student' => $formattedStudent,
            'sections' => $sections
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        // Add debugging
        Log::info('Update method called for student ID: ' . $student->id);
        Log::info('Request data: ' . json_encode($request->all()));
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'required|string|max:50',
            'student_number' => 'required|string|max:20',
            'section_id' => 'required|integer|exists:sections,section_id',
            'specialization' => 'required|string|in:BA,WMAD,SM',
            'email' => 'required|email|max:255',
        ]);

        Log::info('Validated data: ' . json_encode($validated));

        // Update student data
        $student->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'student_number' => $validated['student_number'],
            'section_id' => $validated['section_id'],
            'specialization' => $validated['specialization'],
        ]);

        // Update user email if provided
        if ($student->user && $validated['email']) {
            $student->user->update(['email' => $validated['email']]);
        }

        return redirect()->route('student-list')->with('success', 'Student updated successfully');
    }

    /**
     * Archive the specified student (soft delete by setting is_active to false).
     */
    public function archive(Student $student)
    {
        $student->update(['is_active' => false]);
        
        return redirect()->route('student-list')->with('success', 'Student archived successfully');
    }

    /**
     * Restore the specified archived student (set is_active to true).
     */
    public function restore(Student $student)
    {
        $student->update(['is_active' => true]);
        
        return redirect()->route('student-list')->with('success', 'Student restored successfully');
    }

    /**
     * Edit unverified user account
     */
    public function editUnverifiedUser(User $user)
    {
        $user->load(['academeAccounts.section']);
        $academeAccount = $user->academeAccounts()->first();
        
        // Get all available sections for the dropdown
        $sections = \App\Models\Section::where('status', 'active')
            ->orderBy('section_name')
            ->get(['section_id', 'section_name']);
        
        $formattedUser = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'status' => $user->status,
            'section_id' => $academeAccount ? $academeAccount->section_id : null,
            'section' => $academeAccount ? $academeAccount->section->section_name ?? '' : '',
            'created_at' => $user->created_at,
        ];
        
        return Inertia::render('admin/student/edit-unverified', [
            'user' => $formattedUser,
            'sections' => $sections
        ]);
    }

    /**
     * Update unverified user account
     */
    public function updateUnverifiedUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'section_id' => 'required|integer|exists:sections,section_id',
        ]);

        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        // Update academe account section
        $academeAccount = $user->academeAccounts()->first();
        if ($academeAccount) {
            $academeAccount->update(['section_id' => $validated['section_id']]);
        }

        return redirect()->route('student-unverified')->with('success', 'Unverified user updated successfully');
    }

    /**
     * Archive unverified user account
     */
    public function archiveUnverifiedUser(User $user)
    {
        $user->update(['status' => 'archived']);
        
        return redirect()->route('student-unverified')->with('success', 'Unverified user archived successfully');
    }

    /**
     * Restore archived unverified user account
     */
    public function restoreUnverifiedUser(User $user)
    {
        $user->update(['status' => 'unverified']);
        
        return redirect()->route('student-list')->with('success', 'Unverified user restored successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }

    /**
     * Get matched students with their highest compatibility scores
     */
    public function getMatchedStudents(Request $request)
    {
        $sectionFilter = $request->get('section');
        $internshipFilter = $request->get('internship');
        $searchQuery = $request->get('search');

        // Get students who have submitted assessments and are not yet placed
        $query = Student::with(['user', 'scores.subcategory', 'section'])
            ->where('is_submit', true)
            ->where('is_active', true)
            ->where('is_placed', false); // Exclude students who are already placed

        // Apply section filter
        if ($sectionFilter && $sectionFilter !== 'all') {
            $query->whereHas('section', function($q) use ($sectionFilter) {
                $q->where('section_name', $sectionFilter);
            });
        }

        // Apply search filter
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('first_name', 'like', "%{$searchQuery}%")
                  ->orWhere('last_name', 'like', "%{$searchQuery}%")
                  ->orWhere('student_number', 'like', "%{$searchQuery}%");
            });
        }

        // Get students who don't have any current endorsed endorsements
        // This includes students with no endorsements, only rejected endorsements, or mixed statuses
        $students = $query->whereDoesntHave('endorsements', function($q) {
            $q->where('status', 'endorsed');
        })->get();

        $matchedStudents = $students->map(function ($student) use ($internshipFilter) {
            if ($internshipFilter && $internshipFilter !== 'all') {
                // If specific internship is selected, get compatibility score for that internship
                // Show only students who haven't been endorsed yet
                $specificMatch = $student->compatibilityScores()
                    ->with(['internship.hte:id,company_name', 'internship.subcategoryWeights.subcategory'])
                    ->where('internship_id', $internshipFilter)
                    ->where('endorsement_status', 'pending')
                    ->first();

                if ($specificMatch) {
                    return [
                        'id' => $student->id,
                        'student_number' => $student->student_number,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'middle_name' => $student->middle_name,
                        'section' => $student->section->section_name ?? '',
                        'specialization' => $student->specialization,
                        'best_match' => [
                            'internship' => [
                                'id' => $specificMatch->internship->id,
                                'position_title' => $specificMatch->internship->position_title,
                                'department' => $specificMatch->internship->department,
                                'slot_count' => $specificMatch->internship->slot_count,
                                'hte' => [
                                    'company_name' => $specificMatch->internship->hte->company_name ?? 'Unknown Company'
                                ]
                            ],
                            'compatibility_score' => $specificMatch->compatibility_score,
                            'status' => $specificMatch->status, // Include status for frontend display
                        ],
                        'has_matches' => true,
                    ];
                }
            } else {
                // Get the best match from stored compatibility scores
                // Show only students who haven't been endorsed yet
                $bestMatch = $student->compatibilityScores()
                    ->with(['internship.hte:id,company_name', 'internship.subcategoryWeights.subcategory'])
                    ->where('endorsement_status', 'pending')
                    ->orderBy('compatibility_score', 'desc')
                    ->get()
                    ->filter(function ($match) {
                        // Calculate available slots (total slots - approved placements)
                        $availableSlots = $match->internship->slot_count - 
                            $match->internship->studentPlacements()->where('status', 'approved')->count();
                        return $availableSlots > 0;
                    })
                    ->first();

                if ($bestMatch) {
                    return [
                        'id' => $student->id,
                        'student_number' => $student->student_number,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'middle_name' => $student->middle_name,
                        'section' => $student->section->section_name ?? '',
                        'specialization' => $student->specialization,
                        'best_match' => [
                            'internship' => [
                                'id' => $bestMatch->internship->id,
                                'position_title' => $bestMatch->internship->position_title,
                                'department' => $bestMatch->internship->department,
                                'slot_count' => $bestMatch->internship->slot_count,
                                'available_slots' => $bestMatch->internship->slot_count - $bestMatch->internship->studentPlacements()->where('status', 'approved')->count(),
                                'occupied_slots' => $bestMatch->internship->studentPlacements()->where('status', 'approved')->count(),
                                'hte' => [
                                    'company_name' => $bestMatch->internship->hte->company_name ?? 'Unknown Company'
                                ]
                            ],
                            'compatibility_score' => $bestMatch->compatibility_score,
                            'status' => $bestMatch->status, // Include status for frontend display
                        ],
                        'has_matches' => true,
                    ];
                }
            }

            return [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'section' => $student->section->section_name ?? '',
                'specialization' => $student->specialization,
                'best_match' => null,
                'has_matches' => false,
            ];
        })
        ->filter(function ($student) {
            // Only include students who have at least one match
            return $student['has_matches'];
        });

        // Sort based on filter type
        if ($internshipFilter && $internshipFilter !== 'all') {
            // Sort by rank for specific internship
            $matchedStudents = $matchedStudents->sortBy(function ($student) {
                return $student['best_match']['compatibility_score'];
            })->reverse()->values();
        } else {
            // Sort by compatibility score (highest first) for section-based view
            $matchedStudents = $matchedStudents->sortByDesc(function ($student) {
                return $student['best_match']['compatibility_score'];
            })->values();
        }

        // Get available sections with placement counts
        $availableSections = Student::with('section')
            ->where('is_submit', true)
            ->where('is_active', true)
            ->get()
            ->groupBy('section.section_name')
            ->map(function ($sectionStudents, $sectionName) {
                if (!$sectionName) return null;
                
                $totalStudents = $sectionStudents->count();
                $placedStudents = $sectionStudents->filter(function ($student) {
                    return $student->is_placed || $student->placements()->where('status', 'approved')->exists();
                })->count();
                
                return [
                    'name' => $sectionName,
                    'total_students' => $totalStudents,
                    'placed_students' => $placedStudents,
                    'placement_rate' => $totalStudents > 0 ? round(($placedStudents / $totalStudents) * 100, 1) : 0
                ];
            })
            ->filter()
            ->values();

        // Get available internships with slot occupancy information
        $availableInternships = Internship::where('is_active', true)
            ->where('slot_count', '>', 0)
            ->with(['hte:id,company_name', 'studentPlacements'])
            ->get()
            ->map(function ($internship) {
                $occupiedSlots = $internship->studentPlacements()
                    ->where('status', 'approved')
                    ->count();
                
                $availableSlots = $internship->slot_count - $occupiedSlots;
                $occupancyRate = $internship->slot_count > 0 ? round(($occupiedSlots / $internship->slot_count) * 100, 1) : 0;
                
                return [
                    'id' => $internship->id,
                    'title' => $internship->position_title,
                    'company' => $internship->hte->company_name,
                    'department' => $internship->department,
                    'total_slots' => $internship->slot_count,
                    'occupied_slots' => $occupiedSlots,
                    'available_slots' => $availableSlots,
                    'occupancy_rate' => $occupancyRate
                ];
            });

        return Inertia::render('admin/student/matched', [
            'matchedStudents' => $matchedStudents,
            'filters' => [
                'sections' => $availableSections,
                'internships' => $availableInternships,
                'currentSection' => $sectionFilter,
                'currentInternship' => $internshipFilter,
                'currentSearch' => $searchQuery,
            ]
        ]);
    }

    /**
     * Get all compatibility scores for a student with dynamic sorting
     */
    public function getStudentCompatibilityScores(Request $request, Student $student)
    {
        $student->load('section');
        
        $sortBy = $request->get('sort_by', 'compatibility_score');
        $sortOrder = $request->get('sort_order', 'desc');

        $matchingService = new \App\Services\MatchingService();
        $scores = $matchingService->getCompatibilityScoresSorted($student, $sortBy, $sortOrder);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'section' => $student->section->section_name ?? '',
                'specialization' => $student->specialization,
            ],
            'compatibility_scores' => $scores->map(function ($score) {
                return [
                    'internship' => [
                        'id' => $score['internship']->id,
                        'position_title' => $score['internship']->position_title,
                        'company_name' => $score['internship']->hte->company_name,
                        'department' => $score['internship']->department,
                        'slot_count' => $score['internship']->slot_count,
                        'is_active' => $score['internship']->is_active,
                    ],
                    'compatibility_score' => $score['compatibility_score'],
                    'rank' => $score['rank'],
                ];
            }),
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Calculate compatibility score between a student and an internship
     */
    private function calculateCompatibilityScore($student, $internship): float
    {
        $totalScore = 0;
        $totalWeight = 0;

        // Get the weights for this internship
        $weights = $internship->subcategoryWeights;

        foreach ($weights as $weight) {
            $subcategoryId = $weight->subcategory_id;
            $weightValue = $weight->weight;
            
            // Get student's score for this subcategory
            $studentScore = $student->scores->where('sub_category_id', $subcategoryId)->first();
            
            if ($studentScore) {
                // Convert student score (1-5 scale) to percentage (0-100)
                $scorePercentage = ($studentScore->score / 5) * 100;
                
                // Apply weight to the score
                $weightedScore = $scorePercentage * ($weightValue / 100);
                
                $totalScore += $weightedScore;
                $totalWeight += $weightValue;
            }
        }

        // Calculate final compatibility score
        if ($totalWeight > 0) {
            return round(($totalScore / $totalWeight) * 100, 2);
        }

        return 0;
    }

    /**
     * Get student details with assessment scores and internship criteria
     */
    public function getStudentDetails(Student $student)
    {
        $student->load([
            'scores.subcategory.category',
            'user',
            'section'
        ]);

        // Get the best matching internship
        $activeInternships = Internship::with(['hte:id,company_name', 'subcategoryWeights.subcategory.category'])
            ->where('is_active', true)
            ->where('slot_count', '>', 0)
            ->get();

        $bestMatch = null;
        $highestScore = 0;

        foreach ($activeInternships as $internship) {
            $compatibilityScore = $this->calculateCompatibilityScore($student, $internship);
            
            if ($compatibilityScore > $highestScore) {
                $highestScore = $compatibilityScore;
                $bestMatch = [
                    'internship' => $internship,
                    'compatibility_score' => $compatibilityScore,
                ];
            }
        }

        // Get detailed scores breakdown
        $scoresBreakdown = $student->scores->map(function ($score) {
            return [
                'category' => $score->subcategory->category->name,
                'subcategory' => $score->subcategory->name,
                'score' => $score->score,
                'score_percentage' => ($score->score / 5) * 100,
            ];
        });

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'section' => $student->section->section_name ?? '',
                'specialization' => $student->specialization,
            ],
            'best_match' => $bestMatch,
            'scores_breakdown' => $scoresBreakdown,
        ]);
    }

    // Note: Removed checkInefficientSlots method as it was preventing valid placements

    /**
     * Endorse student for HTE approval
     */
    public function endorseStudent(Request $request, Student $student)
    {
        // Debug logging
        Log::info('Placement approval request received', [
            'student_id' => $student->id,
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->url(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::id() ?? 'not_authenticated',
            'user_email' => Auth::user()?->email ?? 'unknown'
        ]);

        // Log the raw request data for debugging
        Log::info('Raw request data:', [
            'has_admin_notes' => $request->has('admin_notes'),
            'admin_notes_value' => $request->input('admin_notes'),
            'all_inputs' => $request->all(),
            'content_type' => $request->header('Content-Type'),
            'csrf_token' => $request->header('X-CSRF-TOKEN')
        ]);

        // Check if request is JSON
        if ($request->isJson()) {
            Log::info('Request is JSON format');
            $jsonData = $request->json()->all();
            Log::info('JSON data:', $jsonData);
        } else {
            Log::info('Request is not JSON format');
        }

        $validated = $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'compatibility_score' => 'required|numeric|min:0|max:100',
        ]);

        // Log validated data for debugging
        Log::info('Validated data:', [
            'validated' => $validated
        ]);

        // Check if student already has a placement
        $existingPlacement = StudentPlacement::where('student_id', $student->id)->first();
        if ($existingPlacement) {
            return response()->json([
                'message' => 'Student already has a placement'
            ], 400);
        }

        // Check if student already has an endorsement for this internship
        $existingEndorsement = Endorsement::where('student_id', $student->id)
            ->where('internship_id', $validated['internship_id'])
            ->where('status', 'endorsed')
            ->first();
        if ($existingEndorsement) {
            return response()->json([
                'message' => 'Student already has an endorsement for this internship'
            ], 400);
        }

        // Check if internship exists
        $internship = Internship::find($validated['internship_id']);
        if (!$internship) {
            return response()->json([
                'message' => 'Internship not found'
            ], 404);
        }

        try {
            // Update the corresponding student_match record endorsement status to 'endorsed'
            $studentMatchUpdated = StudentMatch::where('student_id', $student->id)
                ->where('internship_id', $validated['internship_id'])
                ->update(['endorsement_status' => 'endorsed']);

            // Create endorsement record
            $endorsement = Endorsement::create([
                'student_id' => $student->id,
                'internship_id' => $validated['internship_id'],
                'status' => 'endorsed',
                'compatibility_score' => $validated['compatibility_score'],
                'endorsement_date' => now(),
            ]);

            // Send notification to HTE about the endorsement
            $internship = Internship::with('hte.user')->find($validated['internship_id']);
            if ($internship && $internship->hte) {
                $notificationService = new NotificationService();
                $studentName = $student->first_name . ' ' . $student->last_name;
                $companyName = $internship->hte->company_name;
                $notificationService->notifyHTEForEndorsement(
                    $internship->hte->user_id,
                    $studentName,
                    $companyName,
                    $student->id,
                    $internship->id
                );

                // Also notify the student about their placement
                $notificationService->notifyStudentForPlacement(
                    $student,
                    $companyName,
                    $internship->position_title,
                    $internship->id
                );
            }

            // Do not auto-fallback on admin endorsement. HTE rejection will drive fallback.
            
            if ($studentMatchUpdated) {
                Log::info('Student match status updated to endorsed');
            } else {
                Log::warning('Student match status update failed or no matching record found');
            }

            Log::info('Student endorsed successfully', [
                'student_id' => $student->id,
                'internship_id' => $validated['internship_id'],
                'endorsement_id' => $endorsement->id,
                'compatibility_score' => $validated['compatibility_score']
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in student endorsement', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Check for specific database errors
            if ($e->getCode() == 23000) { // Integrity constraint violation
                return response()->json([
                    'message' => 'Endorsement already exists for this student and internship'
                ], 400);
            }
            
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error in student endorsement', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error creating endorsement: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Student endorsed successfully',
            'endorsement' => [
                'id' => $endorsement->id,
                'student_id' => $endorsement->student_id,
                'internship_id' => $endorsement->internship_id,
                'status' => $endorsement->status,
                'compatibility_score' => $endorsement->compatibility_score,
                'endorsement_date' => $endorsement->endorsement_date,
                'created_at' => $endorsement->created_at,
            ]
        ]);
    }

    /**
     * Reject student placement and move to next highest compatibility match
     */
    public function rejectPlacement(Request $request, Student $student)
    {
        $validated = $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'compatibility_score' => 'required|numeric|min:0|max:100',
        ]);

        try {
            // Update the corresponding student_match record endorsement status to 'rejected'
            $studentMatchUpdated = StudentMatch::where('student_id', $student->id)
                ->where('internship_id', $validated['internship_id'])
                ->update(['endorsement_status' => 'rejected']);

            if (!$studentMatchUpdated) {
                return response()->json(['error' => 'Student match not found'], 404);
            }

            // Create endorsement record for the rejected match
            Endorsement::create([
                'student_id' => $student->id,
                'internship_id' => $validated['internship_id'],
                'status' => 'rejected',
                'compatibility_score' => 0, // Set to 0 for rejected endorsements
                'endorsement_date' => now(),
            ]);

            // Find the student's next highest compatibility match
            // Look for pending endorsement matches (not yet endorsed by admin)
            $nextMatch = StudentMatch::with(['internship.hte'])
                ->where('student_id', $student->id)
                ->where('endorsement_status', 'pending')
                ->orderBy('compatibility_score', 'desc')
                ->first();

            if ($nextMatch) {
                // Keep the next match as 'pending' - do NOT auto-endorse
                // This allows the student to appear in admin's list for further endorsement
                // Student will show their new best match in the admin's list

                Log::info('Student moved to next highest compatibility match (pending endorsement):', [
                    'student_id' => $student->id,
                    'old_internship_id' => $validated['internship_id'],
                    'new_internship_id' => $nextMatch->internship_id,
                    'new_hte_id' => $nextMatch->internship->hte_id,
                ]);

                return response()->json([
                    'message' => 'Student endorsement rejected and moved to next highest compatibility match. Student is now available for endorsement.',
                    'fallback' => true,
                    'new_internship' => [
                        'id' => $nextMatch->internship->id,
                        'position_title' => $nextMatch->internship->position_title,
                        'company_name' => $nextMatch->internship->hte->company_name,
                        'compatibility_score' => $nextMatch->compatibility_score
                    ]
                ]);
            } else {
                // No more matches available
                Log::info('Student rejected but no more matches available:', [
                    'student_id' => $student->id,
                    'rejected_internship_id' => $validated['internship_id']
                ]);

                return response()->json([
                    'message' => 'Student endorsement rejected. No more compatible matches available.',
                    'fallback' => false
                ]);
            }

        } catch (\Exception $e) {
            Log::error('SIP Student Rejection Error:', [
                'error' => $e->getMessage(),
                'student_id' => $student->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while rejecting the student'], 500);
        }
    }



    /**
     * Get placed students with filtering options
     */
    public function getPlacedStudents(Request $request)
    {
        $sectionFilter = $request->get('section');
        $internshipFilter = $request->get('internship');
        $searchQuery = $request->get('search');

        // Base query for placed students
        $query = StudentPlacement::with(['student.section', 'internship.hte'])
            ->where('status', 'approved'); // Only show approved placements

        // Apply section filter
        if ($sectionFilter && $sectionFilter !== 'all') {
            $query->whereHas('student.section', function($q) use ($sectionFilter) {
                $q->where('section_name', $sectionFilter);
            });
        }

        // Apply internship filter
        if ($internshipFilter && $internshipFilter !== 'all') {
            $query->where('internship_id', $internshipFilter);
        }

        // Apply search filter
        if ($searchQuery) {
            $query->whereHas('student', function ($q) use ($searchQuery) {
                $q->where('first_name', 'like', "%{$searchQuery}%")
                  ->orWhere('last_name', 'like', "%{$searchQuery}%")
                  ->orWhere('student_number', 'like', "%{$searchQuery}%");
            });
        }

        $placedStudents = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($placement) {
                return [
                    'id' => $placement->id,
                    'student' => [
                        'id' => $placement->student->id,
                        'student_number' => $placement->student->student_number,
                        'first_name' => $placement->student->first_name,
                        'last_name' => $placement->student->last_name,
                        'middle_name' => $placement->student->middle_name,
                        'section' => $placement->student->section->section_name ?? '',
                        'specialization' => $placement->student->specialization,
                    ],
                    'internship' => [
                        'id' => $placement->internship->id,
                        'position_title' => $placement->internship->position_title,
                        'department' => $placement->internship->department,
                        'hte' => [
                            'company_name' => $placement->internship->hte->company_name,
                        ],
                    ],
                    'status' => $placement->status,
                    'compatibility_score' => $placement->compatibility_score,
                    'placement_date' => $placement->placement_date,
                    'created_at' => $placement->created_at,
                ];
            });

        // Get available sections with placement counts
        $availableSections = StudentPlacement::with(['student.section'])
            ->where('status', 'approved')
            ->get()
            ->groupBy('student.section.section_name')
            ->map(function ($sectionPlacements, $sectionName) {
                if (!$sectionName) return null;
                
                $totalPlacements = $sectionPlacements->count();
                
                return [
                    'name' => $sectionName,
                    'total_placements' => $totalPlacements,
                ];
            })
            ->filter()
            ->values();

        // Get available internships with placement counts
        $availableInternships = StudentPlacement::with(['internship.hte'])
            ->where('status', 'approved')
            ->get()
            ->groupBy('internship.id')
            ->map(function ($internshipPlacements, $internshipId) {
                $firstPlacement = $internshipPlacements->first();
                if (!$firstPlacement) return null;
                
                $totalPlacements = $internshipPlacements->count();
                
                return [
                    'id' => $firstPlacement->internship->id,
                    'title' => $firstPlacement->internship->position_title,
                    'company' => $firstPlacement->internship->hte->company_name,
                    'department' => $firstPlacement->internship->department,
                    'total_placements' => $totalPlacements,
                ];
            })
            ->filter()
            ->values();

        return Inertia::render('admin/student/placed', [
            'placedStudents' => $placedStudents,
            'filters' => [
                'sections' => $availableSections,
                'internships' => $availableInternships,
                'currentSection' => $sectionFilter,
                'currentInternship' => $internshipFilter,
                'currentSearch' => $searchQuery,
            ]
        ]);
    }

    /**
     * Check for slot conflicts before bulk approval
     */
    public function checkBatchPlacementConflicts(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'internship_filter' => 'nullable|exists:internships,id'
        ]);

        $studentIds = $validated['student_ids'];
        $internshipFilter = $validated['internship_filter'] ?? null;
        $conflicts = [];
        $studentsWithConflicts = [];
        $approvedStudents = [];
        
        // First, collect all students and their target matches
        $studentMatches = [];
        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);
            if (!$student) {
                continue;
            }
            
            // Check if student already has a placement
            $existingPlacement = StudentPlacement::where('student_id', $student->id)->first();
            if ($existingPlacement) {
                continue;
            }
            
            // If internship filter is specified, get match for that specific internship
            // Otherwise, get student's best match (highest compatibility score)
            if ($internshipFilter) {
                $targetMatch = StudentMatch::where('student_id', $student->id)
                    ->where('internship_id', $internshipFilter)
                    ->with(['internship.hte'])
                    ->first();
            } else {
                $targetMatch = StudentMatch::where('student_id', $student->id)
                    ->with(['internship.hte'])
                    ->orderBy('compatibility_score', 'desc')
                    ->first();
            }
            
            if (!$targetMatch) {
                continue;
            }
            
            $studentMatches[] = [
                'student' => $student,
                'best_match' => $targetMatch
            ];
        }
        
        // Group students by their best match internship
        $internshipGroups = [];
        foreach ($studentMatches as $studentMatch) {
            $internshipId = $studentMatch['best_match']->internship->id;
            if (!isset($internshipGroups[$internshipId])) {
                $internshipGroups[$internshipId] = [];
            }
            $internshipGroups[$internshipId][] = $studentMatch;
        }
        
        // Check each internship group for slot conflicts
        foreach ($internshipGroups as $internshipId => $students) {
            $internship = $students[0]['best_match']->internship;
            
            // Get current available slots
            $currentApprovedPlacements = $internship->studentPlacements()
                ->where('status', 'approved')
                ->count();
            
            $availableSlots = $internship->slot_count - $currentApprovedPlacements;
            $studentsWantingThisInternship = count($students);
            
            // If more students want this internship than available slots
            if ($studentsWantingThisInternship > $availableSlots) {
                // Sort students by compatibility score (highest first)
                usort($students, function($a, $b) {
                    return $b['best_match']->compatibility_score <=> $a['best_match']->compatibility_score;
                });
                
                // Students who will get their best match (top N by compatibility)
                $studentsGettingBestMatch = array_slice($students, 0, $availableSlots);
                
                // Students who will need fallback matches (remaining students)
                $studentsNeedingFallback = array_slice($students, $availableSlots);
                
                // Add students who will be approved to the approved list
                foreach ($studentsGettingBestMatch as $studentMatch) {
                    $student = $studentMatch['student'];
                    $bestMatch = $studentMatch['best_match'];
                    
                    $approvedStudents[] = [
                        'student_id' => $student->id,
                        'student_name' => "{$student->first_name} {$student->last_name}",
                        'internship_title' => $bestMatch->internship->position_title,
                        'company_name' => $bestMatch->internship->hte->company_name ?? 'Unknown Company',
                        'compatibility_score' => $bestMatch->compatibility_score,
                        'match_rank' => '1st match'
                    ];
                }
                
                // Process students who need fallback matches
                foreach ($studentsNeedingFallback as $studentMatch) {
                    $student = $studentMatch['student'];
                    $bestMatch = $studentMatch['best_match'];
                    
                    // Get all matches for this student to determine fallback rank
                    $allMatches = StudentMatch::where('student_id', $student->id)
                        ->with(['internship.hte'])
                        ->orderBy('compatibility_score', 'desc')
                        ->get();
                    
                    // Find the best available match (with slots) considering simulated placements
                    $fallbackMatch = null;
                    $fallbackRank = 'Other';
                    
                    foreach ($allMatches as $index => $match) {
                        // Skip the best match (first match) as it's already unavailable
                        if ($match->internship->id === $bestMatch->internship->id) {
                            continue;
                        }
                        
                        // Calculate available slots considering current approved placements and simulated placements
                        $currentApprovedPlacements = $match->internship->studentPlacements()->where('status', 'approved')->count();
                        $simulatedPlacementsForThisInternship = isset($simulatedPlacements[$match->internship->id]) ? $simulatedPlacements[$match->internship->id] : 0;
                        $availableSlots = $match->internship->slot_count - $currentApprovedPlacements - $simulatedPlacementsForThisInternship;
                        
                        if ($availableSlots > 0) {
                            $fallbackMatch = $match;
                            $rankNumber = $index + 1;
                            if ($rankNumber == 2) {
                                $fallbackRank = '2nd match';
                            } elseif ($rankNumber == 3) {
                                $fallbackRank = '3rd match';
                            } elseif ($rankNumber == 4) {
                                $fallbackRank = '4th match';
                            } else {
                                $fallbackRank = 'Other';
                            }
                            
                            // Simulate this placement for future calculations
                            if (!isset($simulatedPlacements[$match->internship->id])) {
                                $simulatedPlacements[$match->internship->id] = 0;
                            }
                            $simulatedPlacements[$match->internship->id]++;
                            break;
                        }
                    }
                    
                    if ($fallbackMatch) {
                        $conflicts[] = [
                            'student_id' => $student->id,
                            'student_name' => "{$student->first_name} {$student->last_name}",
                            'best_match' => [
                                'internship_id' => $bestMatch->internship->id,
                                'position_title' => $bestMatch->internship->position_title,
                                'company_name' => $bestMatch->internship->hte->company_name ?? 'Unknown Company',
                                'compatibility_score' => $bestMatch->compatibility_score,
                                'available_slots' => $availableSlots - count($studentsGettingBestMatch), // Show remaining slots after best match students are placed
                                'students_competing' => $studentsWantingThisInternship
                            ],
                            'fallback_match' => [
                                'internship_id' => $fallbackMatch->internship->id,
                                'position_title' => $fallbackMatch->internship->position_title,
                                'company_name' => $fallbackMatch->internship->hte->company_name ?? 'Unknown Company',
                                'compatibility_score' => $fallbackMatch->compatibility_score,
                                'available_slots' => $fallbackMatch->internship->slot_count - 
                                    $fallbackMatch->internship->studentPlacements()->where('status', 'approved')->count() - 
                                    (isset($simulatedPlacements[$fallbackMatch->internship->id]) ? $simulatedPlacements[$fallbackMatch->internship->id] : 0),
                                'match_rank' => $fallbackRank
                            ]
                        ];
                        $studentsWithConflicts[] = $student->id;
                    }
                }
            } else {
                // No conflicts for this internship, all students get their best match
                foreach ($students as $studentMatch) {
                    $student = $studentMatch['student'];
                    $bestMatch = $studentMatch['best_match'];
                    
                    $approvedStudents[] = [
                        'student_id' => $student->id,
                        'student_name' => "{$student->first_name} {$student->last_name}",
                        'internship_title' => $bestMatch->internship->position_title,
                        'company_name' => $bestMatch->internship->hte->company_name ?? 'Unknown Company',
                        'compatibility_score' => $bestMatch->compatibility_score,
                        'match_rank' => '1st match'
                    ];
                }
            }
        }
        
        return response()->json([
            'has_conflicts' => count($conflicts) > 0,
            'conflicts' => $conflicts,
            'approved_students' => $approvedStudents,
            'students_with_conflicts' => $studentsWithConflicts,
            'total_conflicts' => count($conflicts),
            'total_approved' => count($approvedStudents)
        ]);
    }

    /**
     * Approve multiple student placements with efficiency checks
     */
    public function endorseBatchStudents(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'internship_filter' => 'nullable|exists:internships,id'
        ]);

        $studentIds = $validated['student_ids'];
        $internshipFilter = $validated['internship_filter'] ?? null;
        
        \Log::info('Batch endorse request', [
            'student_ids' => $studentIds,
            'internship_filter' => $internshipFilter,
            'total_students' => count($studentIds)
        ]);
        
        // Note: Removed inefficient slots check as it was preventing valid placements
        // The individual slot availability checks below handle this properly
        
        // Check if all students can be endorsed (no conflicts)
        $errors = [];
        $successfulEndorsements = [];
        
        // Process students in batches to handle slot competition properly
        // First, collect all students and their target matches
        $studentMatches = [];
        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);
            if (!$student) {
                $errors[] = "Student ID {$studentId} not found";
                \Log::warning("Student not found", ['student_id' => $studentId]);
                continue;
            }
            
            \Log::info('Processing student for batch endorse', [
                'student_id' => $student->id,
                'student_name' => "{$student->first_name} {$student->last_name}",
                'internship_filter' => $internshipFilter
            ]);
            
            // Check if student already has a placement
            $existingPlacement = StudentPlacement::where('student_id', $student->id)->first();
            if ($existingPlacement) {
                $errors[] = "Student {$student->first_name} {$student->last_name} already has a placement";
                continue;
            }
            
            // Check if student already has any endorsement (not just for specific internship)
            $existingEndorsement = Endorsement::where('student_id', $student->id)
                ->where('status', 'endorsed')
                ->first();
            if ($existingEndorsement) {
                $errors[] = "Student {$student->first_name} {$student->last_name} already has an endorsement";
                continue;
            }
            
            // If internship filter is specified, get match for that specific internship
            // Otherwise, get student's best match (highest compatibility score)
            // Only get matches with pending endorsement status
            if ($internshipFilter) {
                $targetMatch = StudentMatch::where('student_id', $student->id)
                    ->where('internship_id', $internshipFilter)
                    ->where('endorsement_status', 'pending')
                    ->with(['internship.hte'])
                    ->first();
            } else {
                $targetMatch = StudentMatch::where('student_id', $student->id)
                    ->where('endorsement_status', 'pending')
                    ->with(['internship.hte'])
                    ->orderBy('compatibility_score', 'desc')
                    ->first();
            }
            
            if (!$targetMatch) {
                $errors[] = "Student {$student->first_name} {$student->last_name} has no pending internship matches" . 
                    ($internshipFilter ? " for the selected internship" : "");
                continue;
            }
            
            $studentMatches[] = [
                'student' => $student,
                'best_match' => $targetMatch
            ];
        }
        
        // Group students by their best match internship
        $internshipGroups = [];
        foreach ($studentMatches as $studentMatch) {
            $internshipId = $studentMatch['best_match']->internship->id;
            if (!isset($internshipGroups[$internshipId])) {
                $internshipGroups[$internshipId] = [];
            }
            $internshipGroups[$internshipId][] = $studentMatch;
        }
        
        // Process each internship group
        foreach ($internshipGroups as $internshipId => $students) {
            $internship = $students[0]['best_match']->internship;
            
            // Get current available slots
            $currentApprovedPlacements = $internship->studentPlacements()
                ->where('status', 'approved')
                ->count();
            
            $availableSlots = $internship->slot_count - $currentApprovedPlacements;
            $studentsWantingThisInternship = count($students);
            
            // If more students want this internship than available slots
            if ($studentsWantingThisInternship > $availableSlots) {
                // Sort students by compatibility score (highest first)
                usort($students, function($a, $b) {
                    return $b['best_match']->compatibility_score <=> $a['best_match']->compatibility_score;
                });
                
                // Students who will get their best match (top N by compatibility)
                $studentsGettingBestMatch = array_slice($students, 0, $availableSlots);
                
                // Students who will need fallback matches (remaining students)
                $studentsNeedingFallback = array_slice($students, $availableSlots);
                
                // Process students who get their best match
                foreach ($studentsGettingBestMatch as $studentMatch) {
                    $student = $studentMatch['student'];
                    $bestMatch = $studentMatch['best_match'];
                    
                    try {
                        // Update the corresponding student_match record endorsement status to 'endorsed'
                        StudentMatch::where('student_id', $student->id)
                            ->where('internship_id', $internship->id)
                            ->update(['endorsement_status' => 'endorsed']);

                        // Create endorsement record
                        $endorsement = Endorsement::create([
                            'student_id' => $student->id,
                            'internship_id' => $internship->id,
                            'status' => 'endorsed',
                            'compatibility_score' => $bestMatch->compatibility_score,
                            'endorsement_date' => now(),
                        ]);

                        // Send notification to student about their placement
                        $notificationService = new NotificationService();
                        $notificationService->notifyStudentForPlacement(
                            $student,
                            $internship->hte->company_name,
                            $internship->position_title,
                            $internship->id
                        );
                        
                        $successfulEndorsements[] = [
                            'student_id' => $student->id,
                            'student_name' => "{$student->first_name} {$student->last_name}",
                            'internship_title' => $internship->position_title,
                            'compatibility_score' => $bestMatch->compatibility_score,
                            'match_type' => 'best_match'
                        ];
                        
                    } catch (\Exception $e) {
                        $errors[] = "Failed to place student {$student->first_name} {$student->last_name}: " . $e->getMessage();
                    }
                }
                
                // Process students who need fallback matches
                // Sort students needing fallback by compatibility score (highest first) for consistent processing
                usort($studentsNeedingFallback, function($a, $b) {
                    return $b['best_match']->compatibility_score <=> $a['best_match']->compatibility_score;
                });
                
                foreach ($studentsNeedingFallback as $studentMatch) {
                    $student = $studentMatch['student'];
                    $bestMatch = $studentMatch['best_match'];
                    
                    // Get all matches for this student with pending endorsement status
                    $allMatches = StudentMatch::where('student_id', $student->id)
                        ->where('endorsement_status', 'pending')
                        ->with(['internship.hte'])
                        ->orderBy('compatibility_score', 'desc')
                        ->get();
                    
                    // Find the best available match (with slots) excluding the best match
                    $bestAvailableMatch = null;
                    
                    foreach ($allMatches as $match) {
                        // Skip the best match (first match) as it's already unavailable
                        if ($match->internship->id === $bestMatch->internship->id) {
                            continue;
                        }
                        
                        $availableSlots = $match->internship->slot_count - 
                            $match->internship->studentPlacements()->where('status', 'approved')->count();
                        
                        if ($availableSlots > 0) {
                            $bestAvailableMatch = $match;
                            break;
                        }
                    }
                    
                    if (!$bestAvailableMatch) {
                        $errors[] = "Student {$student->first_name} {$student->last_name} has no internship matches with available slots";
                        continue;
                    }
                    
                    $fallbackInternship = $bestAvailableMatch->internship;
                    
                    try {
                        // Update the corresponding student_match record endorsement status to 'endorsed'
                        StudentMatch::where('student_id', $student->id)
                            ->where('internship_id', $fallbackInternship->id)
                            ->update(['endorsement_status' => 'endorsed']);
                        
                        // Create endorsement record
                        $endorsement = Endorsement::create([
                            'student_id' => $student->id,
                            'internship_id' => $fallbackInternship->id,
                            'status' => 'endorsed',
                            'compatibility_score' => $bestAvailableMatch->compatibility_score,
                            'endorsement_date' => now(),
                        ]);

                        // Send notification to student about their placement
                        $notificationService = new NotificationService();
                        $notificationService->notifyStudentForPlacement(
                            $student,
                            $fallbackInternship->hte->company_name,
                            $fallbackInternship->position_title,
                            $fallbackInternship->id
                        );
                        
                        $successfulEndorsements[] = [
                            'student_id' => $student->id,
                            'student_name' => "{$student->first_name} {$student->last_name}",
                            'internship_title' => $fallbackInternship->position_title,
                            'compatibility_score' => $bestAvailableMatch->compatibility_score,
                            'match_type' => 'fallback_match'
                        ];
                        
                    } catch (\Exception $e) {
                        $errors[] = "Failed to place student {$student->first_name} {$student->last_name} in fallback match: " . $e->getMessage();
                    }
                }
            } else {
                // No conflicts for this internship, all students get their best match
                foreach ($students as $studentMatch) {
                    $student = $studentMatch['student'];
                    $bestMatch = $studentMatch['best_match'];
                    $internship = $bestMatch->internship;
                    
                    try {
                        
                        // Update the corresponding student_match record endorsement status to 'endorsed'
                        StudentMatch::where('student_id', $student->id)
                            ->where('internship_id', $internship->id)
                            ->update(['endorsement_status' => 'endorsed']);

                        // Create endorsement record
                        Endorsement::create([
                            'student_id' => $student->id,
                            'internship_id' => $internship->id,
                            'status' => 'endorsed',
                            'compatibility_score' => $bestMatch->compatibility_score,
                            'endorsement_date' => now(),
                        ]);

                        // Send notification to student about their placement
                        $notificationService = new NotificationService();
                        $notificationService->notifyStudentForPlacement(
                            $student,
                            $internship->hte->company_name,
                            $internship->position_title,
                            $internship->id
                        );
                        
                        $successfulEndorsements[] = [
                            'student_id' => $student->id,
                            'student_name' => "{$student->first_name} {$student->last_name}",
                            'internship_title' => $internship->position_title,
                            'compatibility_score' => $bestMatch->compatibility_score,
                            'match_type' => 'best_match'
                        ];
                        
                    } catch (\Exception $e) {
                        $errors[] = "Error placing student {$student->first_name} {$student->last_name}: " . $e->getMessage();
                    }
                }
            }
        }
        
        if (empty($successfulEndorsements)) {
            return response()->json([
                'message' => 'No endorsements were created due to errors: ' . implode(', ', $errors),
                'type' => 'all_failed'
            ], 400);
        }
        
        $responseMessage = "Successfully endorsed " . count($successfulEndorsements) . " student(s).";
        if (!empty($errors)) {
            $responseMessage .= " Errors: " . implode(', ', $errors);
        }
        
        return response()->json([
            'message' => $responseMessage,
            'successful_endorsements' => $successfulEndorsements,
            'errors' => $errors,
            'total_requested' => count($studentIds),
            'total_endorsed' => count($successfulEndorsements),
            'total_errors' => count($errors)
        ]);
    }

    /**
     * Reject student endorsement and move to next highest compatibility match
     */
    public function rejectEndorsement(Request $request, Student $student)
    {
        $validated = $request->validate([
            'internship_id' => 'required|exists:internships,id',
        ]);

        try {
            // Update the corresponding student_match record endorsement status to 'rejected'
            $studentMatchUpdated = StudentMatch::where('student_id', $student->id)
                ->where('internship_id', $validated['internship_id'])
                ->update(['endorsement_status' => 'rejected']);

            if (!$studentMatchUpdated) {
                return response()->json(['error' => 'Student match not found'], 404);
            }

            // Create endorsement record for the rejected match
            Endorsement::create([
                'student_id' => $student->id,
                'internship_id' => $validated['internship_id'],
                'status' => 'rejected',
                'compatibility_score' => 0, // Set to 0 for rejected endorsements
                'endorsement_date' => now(),
            ]);

            // Find the student's next highest compatibility match
            // Look for pending endorsement matches (not yet endorsed by admin)
            $nextMatch = StudentMatch::with(['internship.hte'])
                ->where('student_id', $student->id)
                ->where('endorsement_status', 'pending')
                ->orderBy('compatibility_score', 'desc')
                ->first();

            if ($nextMatch) {
                // Keep the next match as 'pending' - do NOT auto-endorse
                // This allows the student to appear in admin's list for further endorsement
                // Student will show their new best match in the admin's list

                Log::info('Student moved to next highest compatibility match (pending endorsement):', [
                    'student_id' => $student->id,
                    'old_internship_id' => $validated['internship_id'],
                    'new_internship_id' => $nextMatch->internship_id,
                    'new_hte_id' => $nextMatch->internship->hte_id,
                ]);

                return response()->json([
                    'message' => 'Student endorsement rejected and moved to next highest compatibility match. Student is now available for endorsement.',
                    'fallback' => true,
                    'new_internship' => [
                        'id' => $nextMatch->internship->id,
                        'position_title' => $nextMatch->internship->position_title,
                        'company_name' => $nextMatch->internship->hte->company_name,
                        'compatibility_score' => $nextMatch->compatibility_score
                    ]
                ]);
            } else {
                // No more matches available
                Log::info('Student rejected but no more matches available:', [
                    'student_id' => $student->id,
                    'rejected_internship_id' => $validated['internship_id']
                ]);

                return response()->json([
                    'message' => 'Student endorsement rejected. No more compatible matches available.',
                    'fallback' => false
                ]);
            }

        } catch (\Exception $e) {
            Log::error('SIP Student Rejection Error:', [
                'error' => $e->getMessage(),
                'student_id' => $student->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while rejecting the student'], 500);
        }
    }

    // Removed admin-side auto-fallback; HTE rejection triggers fallback

    /**
     * Show endorsed students with filtering options
     */
    public function getEndorsedStudents(Request $request)
    {
        $sectionFilter = $request->get('section');
        $internshipFilter = $request->get('internship');
        $searchQuery = $request->get('search');
        $statusFilter = $request->get('status', 'all');

        // Base query for endorsed students
        $query = Endorsement::with(['student.section', 'internship.hte'])
            ->where('status', 'endorsed'); // Only show endorsed students

        // Apply section filter
        if ($sectionFilter && $sectionFilter !== 'all') {
            $query->whereHas('student.section', function($q) use ($sectionFilter) {
                $q->where('section_name', $sectionFilter);
            });
        }

        // Apply internship filter
        if ($internshipFilter && $internshipFilter !== 'all') {
            $query->where('internship_id', $internshipFilter);
        }

        // Apply search filter
        if ($searchQuery) {
            $query->whereHas('student', function ($q) use ($searchQuery) {
                $q->where('first_name', 'like', "%{$searchQuery}%")
                  ->orWhere('last_name', 'like', "%{$searchQuery}%")
                  ->orWhere('student_number', 'like', "%{$searchQuery}%");
            });
        }

        // Apply status filter (endorsement status vs placement status)
        if ($statusFilter && $statusFilter !== 'all') {
            if ($statusFilter === 'pending_hte') {
                // Show endorsed but not yet approved/rejected by HTE
                $query->whereHas('studentMatch', function($q) {
                    $q->where('placement_status', 'pending');
                });
            } elseif ($statusFilter === 'approved_hte') {
                // Show endorsed and approved by HTE
                $query->whereHas('studentMatch', function($q) {
                    $q->where('placement_status', 'approved');
                });
            } elseif ($statusFilter === 'rejected_hte') {
                // Show endorsed but rejected by HTE
                $query->whereHas('studentMatch', function($q) {
                    $q->where('placement_status', 'rejected');
                });
            }
        }

        $endorsedStudents = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($endorsement) {
                // Get the corresponding student match for placement status
                $studentMatch = \App\Models\StudentMatch::where('student_id', $endorsement->student_id)
                    ->where('internship_id', $endorsement->internship_id)
                    ->first();

                return [
                    'id' => $endorsement->id,
                    'student' => [
                        'id' => $endorsement->student->id,
                        'student_number' => $endorsement->student->student_number,
                        'first_name' => $endorsement->student->first_name,
                        'last_name' => $endorsement->student->last_name,
                        'middle_name' => $endorsement->student->middle_name,
                        'section' => $endorsement->student->section->section_name ?? '',
                        'specialization' => $endorsement->student->specialization,
                    ],
                    'internship' => [
                        'id' => $endorsement->internship->id,
                        'position_title' => $endorsement->internship->position_title,
                        'department' => $endorsement->internship->department,
                        'hte' => [
                            'company_name' => $endorsement->internship->hte->company_name,
                        ],
                    ],
                    'endorsement_status' => $endorsement->status,
                    'placement_status' => $studentMatch ? $studentMatch->placement_status : 'pending',
                    'compatibility_score' => $endorsement->compatibility_score,
                    'endorsement_date' => $endorsement->endorsement_date,
                    'created_at' => $endorsement->created_at,
                ];
            });

        // Get section options for filter
        $sectionOptions = Endorsement::with(['student.section'])
            ->where('status', 'endorsed')
            ->get()
            ->groupBy('student.section.section_name')
            ->map(function ($endorsements, $sectionName) {
                return [
                    'name' => $sectionName,
                    'total_endorsements' => $endorsements->count(),
                ];
            })
            ->values();

        // Get internship options for filter
        $internshipOptions = Endorsement::with(['internship.hte'])
            ->where('status', 'endorsed')
            ->get()
            ->groupBy('internship.id')
            ->map(function ($endorsements, $internshipId) {
                $internship = $endorsements->first()->internship;
                return [
                    'id' => $internship->id,
                    'position_title' => $internship->position_title,
                    'department' => $internship->department,
                    'company_name' => $internship->hte->company_name,
                    'total_endorsements' => $endorsements->count(),
                ];
            })
            ->values();

        return Inertia::render('admin/student/endorsed', [
            'endorsed_students' => $endorsedStudents,
            'section_options' => $sectionOptions,
            'internship_options' => $internshipOptions,
            'filters' => [
                'section' => $sectionFilter,
                'internship' => $internshipFilter,
                'search' => $searchQuery,
                'status' => $statusFilter,
            ],
        ]);
    }


}

