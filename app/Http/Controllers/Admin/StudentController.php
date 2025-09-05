<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentMatch;
use App\Models\Internship;
use App\Models\StudentScore;
use App\Models\SubcategoryWeight;
use App\Models\StudentPlacement;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\MatchingService;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::with('section')
            ->select([
                'id',
                'student_number',
                'first_name',
                'middle_name',
                'last_name',
                'section_id',
                'specialization',
                'is_active'
            ])
            ->where('is_active', true)
            ->orderBy('last_name')
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
        $archivedStudents = Student::with('section')
            ->select([
                'id',
                'student_number',
                'first_name',
                'middle_name',
                'last_name',
                'section_id',
                'specialization',
                'is_active'
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

        return Inertia::render('admin/student/list', [
            'students' => $transformedStudents,
            'unverifiedUsers' => $transformedUnverifiedUsers,
            'archivedStudents' => $transformedArchivedStudents,
            'archivedUnverifiedUsers' => $transformedArchivedUnverifiedUsers
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
        
        $student->load('section');
        
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
            'specialization' => 'required|string|max:100',
        ]);

        Log::info('Validated data: ' . json_encode($validated));

        $student->update($validated);

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

        $students = $query->get();

        $matchedStudents = $students->map(function ($student) use ($internshipFilter) {
            if ($internshipFilter && $internshipFilter !== 'all') {
                // If specific internship is selected, get compatibility score for that internship
                $specificMatch = $student->compatibilityScores()
                    ->with(['internship.hte:id,company_name', 'internship.subcategoryWeights.subcategory'])
                    ->where('internship_id', $internshipFilter)
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
                // Get the best match from stored compatibility scores with pending status
                // Filter out internships with no available slots
                $bestMatch = $student->compatibilityScores()
                    ->with(['internship.hte:id,company_name', 'internship.subcategoryWeights.subcategory'])
                    ->where('status', 'pending') // Only show pending matches for "all internships" filter
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
     * Approve student placement
     */
    public function approvePlacement(Request $request, Student $student)
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

        // Check if internship exists and has available slots
        $internship = Internship::find($validated['internship_id']);
        if (!$internship) {
            return response()->json([
                'message' => 'Internship not found'
            ], 404);
        }

        // Note: Removed inefficient slots check as it was preventing valid placements
        // The slot availability check below handles this properly

        // Check if internship still has available slots
        $currentApprovedPlacements = $internship->studentPlacements()
            ->where('status', 'approved')
            ->count();
        
        // Calculate available slots: total slots minus currently approved placements
        $availableSlots = $internship->slot_count - $currentApprovedPlacements;
        
        // Debug logging for slot calculation
        Log::info('Single placement slot availability check', [
            'internship_id' => $internship->id,
            'position_title' => $internship->position_title,
            'slot_count' => $internship->slot_count,
            'current_approved_placements' => $currentApprovedPlacements,
            'available_slots' => $availableSlots,
            'student_id' => $student->id,
            'student_name' => "{$student->first_name} {$student->last_name}"
        ]);
        
        if ($availableSlots <= 0) {
            return response()->json([
                'message' => "No available slots for this internship. Only {$internship->slot_count} total slots, {$currentApprovedPlacements} already occupied."
            ], 422);
        }

        try {
            // Create placement record using DB::table instead of Eloquent create()
            // This works better with composite primary keys
            $placementData = [
                'student_id' => $student->id,
                'internship_id' => $validated['internship_id'],
                'status' => 'approved',
                'compatibility_score' => $validated['compatibility_score'],
                'placement_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Log the placement data being inserted
            Log::info('Placement data to be inserted:', $placementData);
            
            // Ensure all required fields are present and properly formatted
            if (!isset($placementData['student_id']) || !isset($placementData['internship_id']) || !isset($placementData['compatibility_score'])) {
                throw new \Exception('Missing required placement data fields');
            }

            // Ensure data types are correct
            if (!is_numeric($placementData['compatibility_score']) || $placementData['compatibility_score'] < 0 || $placementData['compatibility_score'] > 100) {
                throw new \Exception('Invalid compatibility score');
            }

            if (!is_numeric($placementData['student_id']) || !is_numeric($placementData['internship_id'])) {
                throw new \Exception('Invalid student or internship ID');
            }
            
            Log::info('Attempting to create placement with data:', $placementData);
            
            $inserted = DB::table('student_placements')->insert($placementData);
            
            if (!$inserted) {
                throw new \Exception('Failed to insert placement record');
            }
            
            Log::info('Placement record inserted successfully');
            
            // Get the created placement for response
            $placement = StudentPlacement::where('student_id', $student->id)
                                ->where('internship_id', $validated['internship_id'])
                                ->first();

            if (!$placement) {
                throw new \Exception('Placement record was inserted but could not be retrieved');
            }

            Log::info('Placement created successfully', ['placement_id' => $placement->getAttributes()]);

            // Update student status
            $studentUpdated = $student->update(['is_placed' => true]);
            if (!$studentUpdated) {
                throw new \Exception('Failed to update student status');
            }
            Log::info('Student status updated');

            // Update the corresponding student_match record status to 'approved'
            $studentMatchUpdated = StudentMatch::where('student_id', $student->id)
                ->where('internship_id', $validated['internship_id'])
                ->update(['status' => 'approved']);
            
            if ($studentMatchUpdated) {
                Log::info('Student match status updated to approved');
            } else {
                Log::warning('Student match status update failed or no matching record found');
            }

            // Note: slot_count is not decremented as it represents total available slots
            // Available slots are calculated dynamically as slot_count - current_approved_placements
            Log::info('Placement approved successfully', ['available_slots_remaining' => $internship->slot_count - ($currentApprovedPlacements + 1)]);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in placement approval', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Check for specific database errors
            if ($e->getCode() == 23000) { // Integrity constraint violation
                return response()->json([
                    'message' => 'Placement already exists for this student and internship'
                ], 400);
            }
            
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error in placement approval', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error creating placement: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Student placement approved successfully',
            'placement' => [
                'id' => $placement->id,
                'student_id' => $placement->student_id,
                'internship_id' => $placement->internship_id,
                'status' => $placement->status,
                'compatibility_score' => $placement->compatibility_score,
                'placement_date' => $placement->placement_date,
                'created_at' => $placement->created_at,
            ]
        ]);
    }

    /**
     * Reject student placement
     */
    public function rejectPlacement(Request $request, Student $student)
    {
        $validated = $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'compatibility_score' => 'required|numeric|min:0|max:100',
        ]);

        // Check if student already has a placement
        $existingPlacement = StudentPlacement::where('student_id', $student->id)->first();
        if ($existingPlacement) {
            return response()->json([
                'message' => 'Student already has a placement'
            ], 400);
        }

        try {
            // Only update the corresponding student_match record status to 'rejected'
            // Do NOT create a placement record for rejected matches
            $studentMatchUpdated = StudentMatch::where('student_id', $student->id)
                ->where('internship_id', $validated['internship_id'])
                ->update(['status' => 'rejected']);
            
            if ($studentMatchUpdated) {
                Log::info('Student match status updated to rejected');
                
                return response()->json([
                    'message' => 'Student placement rejected successfully',
                    'match_updated' => true
                ]);
            } else {
                Log::warning('Student match status update failed or no matching record found');
                
                return response()->json([
                    'message' => 'No matching record found to reject'
                ], 404);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in placement rejection', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error rejecting placement: ' . $e->getMessage()
            ], 500);
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
    public function approveBatchPlacements(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'internship_filter' => 'nullable|exists:internships,id'
        ]);

        $studentIds = $validated['student_ids'];
        $internshipFilter = $validated['internship_filter'] ?? null;
        
        // Note: Removed inefficient slots check as it was preventing valid placements
        // The individual slot availability checks below handle this properly
        
        // Check if all students can be placed (no conflicts)
        $errors = [];
        $successfulPlacements = [];
        
        // Process students in batches to handle slot competition properly
        // First, collect all students and their target matches
        $studentMatches = [];
        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);
            if (!$student) {
                $errors[] = "Student ID {$studentId} not found";
                continue;
            }
            
            // Check if student already has a placement
            $existingPlacement = StudentPlacement::where('student_id', $student->id)->first();
            if ($existingPlacement) {
                $errors[] = "Student {$student->first_name} {$student->last_name} already has a placement";
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
                $errors[] = "Student {$student->first_name} {$student->last_name} has no internship matches" . 
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
                        // Create placement record
                        $placementData = [
                            'student_id' => $student->id,
                            'internship_id' => $internship->id,
                            'status' => 'approved',
                            'compatibility_score' => $bestMatch->compatibility_score,
                            'placement_date' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        DB::table('student_placements')->insert($placementData);
                        
                        // Update student status
                        $student->update(['is_placed' => true]);
                        
                        // Update the corresponding student_match record status to 'approved'
                        StudentMatch::where('student_id', $student->id)
                            ->where('internship_id', $internship->id)
                            ->update(['status' => 'approved']);
                        
                        $successfulPlacements[] = [
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
                    
                    // Get all matches for this student
                    $allMatches = StudentMatch::where('student_id', $student->id)
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
                        // Create placement record
                        $placementData = [
                            'student_id' => $student->id,
                            'internship_id' => $fallbackInternship->id,
                            'status' => 'approved',
                            'compatibility_score' => $bestAvailableMatch->compatibility_score,
                            'placement_date' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        DB::table('student_placements')->insert($placementData);
                        
                        // Update student status
                        $student->update(['is_placed' => true]);
                        
                        // Update the corresponding student_match record status to 'approved'
                        StudentMatch::where('student_id', $student->id)
                            ->where('internship_id', $fallbackInternship->id)
                            ->update(['status' => 'approved']);
                        
                        $successfulPlacements[] = [
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
                        // Create placement record
                        $placementData = [
                            'student_id' => $student->id,
                            'internship_id' => $internship->id,
                            'status' => 'approved',
                            'compatibility_score' => $bestMatch->compatibility_score,
                            'placement_date' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        DB::table('student_placements')->insert($placementData);
                        
                        // Update student status
                        $student->update(['is_placed' => true]);
                        
                        // Update the corresponding student_match record status to 'approved'
                        StudentMatch::where('student_id', $student->id)
                            ->where('internship_id', $internship->id)
                            ->update(['status' => 'approved']);
                        
                        $successfulPlacements[] = [
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
        
        if (empty($successfulPlacements)) {
            return response()->json([
                'message' => 'No placements were approved due to errors: ' . implode(', ', $errors),
                'type' => 'all_failed'
            ], 400);
        }
        
        $responseMessage = "Successfully approved " . count($successfulPlacements) . " placement(s).";
        if (!empty($errors)) {
            $responseMessage .= " Errors: " . implode(', ', $errors);
        }
        
        return response()->json([
            'message' => $responseMessage,
            'successful_placements' => $successfulPlacements,
            'errors' => $errors,
            'total_requested' => count($studentIds),
            'total_approved' => count($successfulPlacements),
            'total_errors' => count($errors)
        ]);
    }


}

