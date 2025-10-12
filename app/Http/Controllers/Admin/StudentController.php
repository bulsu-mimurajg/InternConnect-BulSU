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
        $assessmentFilter = $request->get('assessment', 'all');
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
                'is_submit',
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

        // Apply assessment filter
        if ($assessmentFilter === 'submitted') {
            $studentsQuery->where('is_submit', true);
        } elseif ($assessmentFilter === 'pending') {
            $studentsQuery->where('is_submit', false);
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
                'is_submit' => $student->is_submit,
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
                'is_submit',
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
                'is_submit' => $student->is_submit,
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
                'assessment' => $assessmentFilter,
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
        // Check if student is already archived
        if (!$student->is_active) {
            return redirect()->route('student-list')->withErrors([
                'error' => 'This student is already archived and cannot be archived again.'
            ]);
        }

        // Archive the student
        $student->update(['is_active' => false]);

        // Reset student_matches endorsement status to pending before deleting endorsements
        StudentMatch::where('student_id', $student->id)
            ->where('endorsement_status', 'endorsed')
            ->update(['endorsement_status' => 'pending']);

        // Delete any active endorsements for this student to free up slots
        // This ensures that archived students don't count toward endorsement limits
        Endorsement::where('student_id', $student->id)
            ->where('status', 'endorsed')
            ->delete();

        // Reset student_matches placement status to pending before deleting placements
        StudentMatch::where('student_id', $student->id)
            ->where('placement_status', 'approved')
            ->update(['placement_status' => 'pending']);

        // Delete any approved placements for this student to free up slots
        // This ensures that archived students don't occupy approved slots
        StudentPlacement::where('student_id', $student->id)
            ->where('status', 'approved')
            ->delete();

        // Reset the student's placement status since they've been removed from placements
        $student->update(['is_placed' => false]);

        return redirect()->route('student-list')->with('success', 'Student archived successfully');
    }

    /**
     * Restore the specified archived student (set is_active to true).
     */
    public function restore(Student $student)
    {
        $student->update(['is_active' => true]);
        
        // Recalculate compatibility scores to ensure fresh matches
        if ($student->is_submit) {
            $matchingService = new \App\Services\MatchingService();
            $matchingService->calculateAndStoreCompatibilityScores($student);
        }
        
        return redirect()->route('student-list')->with('success', 'Student restored successfully and matches recalculated');
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
        // Check if user is already archived
        if ($user->status === 'archived') {
            return redirect()->route('student-list')->withErrors([
                'error' => 'This user is already archived and cannot be archived again.'
            ]);
        }

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

        // Get students who don't have any current active endorsed endorsements
        // This includes students with no endorsements, only rejected endorsements, or mixed statuses
        // We exclude students who have endorsements with status 'endorsed' that haven't been processed by HTE yet
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
                // Get the best available match from stored compatibility scores
                // Show only students who haven't been endorsed yet
                $allMatches = $student->compatibilityScores()
                    ->with(['internship.hte:id,company_name', 'internship.subcategoryWeights.subcategory'])
                    ->where('endorsement_status', 'pending')
                    ->orderBy('compatibility_score', 'desc')
                    ->get();

                // Find the best available match (with slots available)
                $bestMatch = null;
                $isFallback = false;
                
                foreach ($allMatches as $match) {
                    // Calculate available slots (total slots - approved placements - endorsed slots)
                    $approvedPlacements = $match->internship->studentPlacements()->where('status', 'approved')->count();
                    $endorsedSlots = Endorsement::where('internship_id', $match->internship->id)
                        ->where('status', 'endorsed')
                        ->count();
                    $availableSlots = $match->internship->slot_count - $approvedPlacements - $endorsedSlots;
                    
                    if ($availableSlots > 0) {
                        $bestMatch = $match;
                        // If this is not the first match (highest compatibility), it's a fallback
                        $isFallback = $match !== $allMatches->first();
                        break;
                    }
                }

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
                                'approved_slots' => $bestMatch->internship->studentPlacements()->where('status', 'approved')->count(),
                                'endorsed_slots' => Endorsement::where('internship_id', $bestMatch->internship->id)->where('status', 'endorsed')->count(),
                                'available_slots' => $bestMatch->internship->slot_count - $bestMatch->internship->studentPlacements()->where('status', 'approved')->count() - Endorsement::where('internship_id', $bestMatch->internship->id)->where('status', 'endorsed')->count(),
                                'hte' => [
                                    'company_name' => $bestMatch->internship->hte->company_name ?? 'Unknown Company'
                                ]
                            ],
                            'compatibility_score' => $bestMatch->compatibility_score,
                            'status' => $bestMatch->status, // Include status for frontend display
                            'is_fallback' => $isFallback, // Indicates if this is a fallback match
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
            // Only include students who have at least one match with available slots
            $hasValidMatch = $student['has_matches'] && $student['best_match'] !== null;
            
            // Log students who are being filtered out due to no available matches
            if (!$hasValidMatch && $student['has_matches'] === false) {
                \Log::info('Student filtered out - no available matches', [
                    'student_id' => $student['id'],
                    'student_name' => $student['first_name'] . ' ' . $student['last_name'],
                    'student_number' => $student['student_number']
                ]);
            }
            
            return $hasValidMatch;
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

        // Get students who couldn't be placed (for admin visibility)
        $unplacedStudents = $this->getUnplacedStudents($sectionFilter, $searchQuery);

        // Calculate statistics for filtered students
        $totalStudentsWithAssessments = $students->count();
        $studentsWithMatches = $matchedStudents->count();
        $studentsWithoutMatches = $unplacedStudents->count();


        return Inertia::render('admin/student/matched', [
            'matchedStudents' => $matchedStudents,
            'unplacedStudents' => array_values($unplacedStudents->toArray()),
            'filters' => [
                'sections' => $availableSections,
                'internships' => $availableInternships,
                'currentSection' => $sectionFilter,
                'currentInternship' => $internshipFilter,
                'currentSearch' => $searchQuery,
            ],
            'statistics' => [
                'total_students_with_assessments' => $totalStudentsWithAssessments,
                'students_with_matches' => $studentsWithMatches,
                'students_without_matches' => $studentsWithoutMatches,
                'unplaced_students' => $unplacedStudents->count(),
            ],
        ]);
    }

    /**
     * Get students who couldn't be placed due to no available slots
     */
    private function getUnplacedStudents($sectionFilter = null, $searchQuery = null)
    {
        $query = Student::with(['section', 'user', 'compatibilityScores.internship.hte', 'compatibilityScores.internship.studentPlacements'])
            ->where('is_submit', true)
            ->where('is_active', true)
            ->where('is_placed', false)
            ->where(function($q) {
                // Include students who:
                // 1. Have no endorsements at all
                // 2. Have only rejected endorsements AND no pending matches with available slots
                $q->whereDoesntHave('endorsements')
                  ->orWhere(function($subQ) {
                      // Students with only rejected endorsements
                      $subQ->whereHas('endorsements', function($endorsementQ) {
                          $endorsementQ->where('status', 'rejected');
                      })->whereDoesntHave('endorsements', function($endorsementQ) {
                          $endorsementQ->where('status', 'endorsed');
                      });
                  });
            });

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

        return $query->get()->filter(function ($student) {
            // Exclude students who still have pending matches with available slots
            // These students should remain in the "matched" category, not "unplaced"
            $hasAvailableMatches = $student->compatibilityScores()
                ->where('endorsement_status', 'pending')
                ->get()
                ->filter(function ($match) {
                    $approvedPlacements = $match->internship->studentPlacements()->where('status', 'approved')->count();
                    $endorsedSlots = Endorsement::where('internship_id', $match->internship->id)
                        ->where('status', 'endorsed')
                        ->count();
                    $availableSlots = $match->internship->slot_count - $approvedPlacements - $endorsedSlots;
                    return $availableSlots > 0;
                })
                ->isNotEmpty();

            // Only include students who truly have no available matches
            return !$hasAvailableMatches;
        })->map(function ($student) {
            // Check if student has been rejected by HTE (placement_status = 'rejected')
            $hteRejectedMatches = \App\Models\StudentMatch::where('student_id', $student->id)
                ->where('placement_status', 'rejected')
                ->get();
            $isHteRejected = $hteRejectedMatches->isNotEmpty();

            // Check if student requires manual intervention
            $unplacedRecord = \App\Models\UnplacedStudent::where('student_id', $student->id)->first();
            $requiresManualIntervention = $unplacedRecord && $unplacedRecord->requires_manual_intervention;

            // Get the reason why student couldn't be placed
            $reason = 'Unknown';
            if ($isHteRejected) {
                $reason = 'Rejected by HTE';
            } elseif ($unplacedRecord) {
                $reason = $unplacedRecord->reason;
            } else {
                $totalMatches = $student->compatibilityScores()->count();
                if ($totalMatches === 0) {
                    $reason = 'No compatibility matches found';
                } else {
                    $reason = 'All matches have no available slots';
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
                'reason' => $reason,
                'has_available_matches' => false, // All students in unplaced category have no available matches
                'requires_manual_intervention' => $requiresManualIntervention,
                'is_hte_rejected' => $isHteRejected,
                'total_matches' => $student->compatibilityScores()->count(),
                'rejected_matches' => $student->compatibilityScores()->where('endorsement_status', 'rejected')->count(),
            ];
        })->filter(function ($student) {
            // Include students who truly couldn't be placed OR have been rejected by HTE
            return !$student['has_available_matches'] || $student['is_hte_rejected'];
        });
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

        // Add slot availability information to the best match
        if ($bestMatch) {
            $internship = $bestMatch['internship'];
            $approvedPlacements = $internship->studentPlacements()->where('status', 'approved')->count();
            $endorsedSlots = Endorsement::where('internship_id', $internship->id)
                ->where('status', 'endorsed')
                ->count();
            $availableSlots = $internship->slot_count - $approvedPlacements - $endorsedSlots;
            
            $bestMatch['available_slots'] = $availableSlots;
            $bestMatch['has_available_slots'] = $availableSlots > 0;
        }

        // Get detailed scores breakdown
        $scoresBreakdown = $student->scores->map(function ($score) {
            return [
                'category' => $score->subcategory->category->category_name ?? 'Uncategorized',
                'subcategory' => $score->subcategory->subcategory_name ?? 'Unknown Subcategory',
                'score' => $score->score,
                'score_percentage' => ($score->score / 5) * 100,
            ];
        })->filter(function ($score) {
            // Filter out any scores that might have null subcategory relationships
            return $score['subcategory'] !== 'Unknown Subcategory';
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

        // Check if internship has available slots for endorsement
        $currentEndorsements = Endorsement::where('internship_id', $validated['internship_id'])
            ->where('status', 'endorsed')
            ->count();
        
        $currentApprovedPlacements = StudentPlacement::where('internship_id', $validated['internship_id'])
            ->where('status', 'approved')
            ->count();
        
        $totalUsedSlots = $currentEndorsements + $currentApprovedPlacements;
        
        if ($totalUsedSlots >= $internship->slot_count) {
            return response()->json([
                'message' => 'No available slots for this internship. All slots are either endorsed or approved.'
            ], 400);
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

                // Don't notify student yet - wait for HTE approval
                // Student will be notified when HTE approves the endorsement
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
            
            // Get student's best available match using consistent logic
            $targetMatch = $this->getBestAvailableMatch($student, $internshipFilter);
            
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
            
            // Get current available slots (accounting for both approved placements AND endorsed slots)
            $currentApprovedPlacements = $internship->studentPlacements()
                ->where('status', 'approved')
                ->count();
            
            $currentEndorsedSlots = Endorsement::where('internship_id', $internship->id)
                ->where('status', 'endorsed')
                ->count();
            
            $availableSlots = $internship->slot_count - $currentApprovedPlacements - $currentEndorsedSlots;
            $studentsWantingThisInternship = count($students);
            
            // Log for debugging
            \Log::info('Checking internship for conflicts', [
                'internship_id' => $internship->id,
                'internship_title' => $internship->position_title,
                'company' => $internship->hte->company_name ?? 'Unknown',
                'total_slots' => $internship->slot_count,
                'approved_placements' => $currentApprovedPlacements,
                'endorsed_slots' => $currentEndorsedSlots,
                'available_slots' => $availableSlots,
                'students_wanting' => $studentsWantingThisInternship,
                'has_conflict' => $studentsWantingThisInternship > $availableSlots
            ]);
            
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
                        'match_rank' => $this->getOrdinalRank($bestMatch->rank) . ' match'
                    ];
                }
                
                // Process students who need fallback matches
                foreach ($studentsNeedingFallback as $studentMatch) {
                    $student = $studentMatch['student'];
                    $bestMatch = $studentMatch['best_match'];
                    
                    // Get all matches for this student ordered by rank (not compatibility score)
                    $allMatches = StudentMatch::where('student_id', $student->id)
                        ->with(['internship.hte'])
                        ->orderBy('rank', 'asc') // Order by rank, not compatibility score
                        ->get();
                    
                    // Find the best available match (with slots) considering simulated placements
                    $fallbackMatch = null;
                    $fallbackRank = 'No fallback available';
                    $relativeRank = 2; // Start with 2nd match
                    
                    foreach ($allMatches as $match) {
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
                            // Show the actual database rank of the fallback match
                            $fallbackRank = $this->getOrdinalRank($relativeRank) . ' match';
                            
                            // Simulate this placement for future calculations
                            if (!isset($simulatedPlacements[$match->internship->id])) {
                                $simulatedPlacements[$match->internship->id] = 0;
                            }
                            $simulatedPlacements[$match->internship->id]++;
                            break;
                        }
                        $relativeRank++;
                    }
                    
                    // Log for debugging fallback rank calculation
                    \Log::info('Calculating fallback rank for student', [
                        'student_id' => $student->id,
                        'student_name' => "{$student->first_name} {$student->last_name}",
                        'best_match_unavailable' => $bestMatch->internship->position_title . ' (' . ($bestMatch->internship->hte->company_name ?? 'Unknown') . ')',
                        'best_match_rank' => $bestMatch->rank,
                        'fallback_match_found' => $fallbackMatch ? $fallbackMatch->internship->position_title . ' (' . ($fallbackMatch->internship->hte->company_name ?? 'Unknown') . ')' : 'None',
                        'fallback_match_db_rank' => $fallbackMatch ? $fallbackMatch->rank : 'N/A',
                        'fallback_display_rank' => $fallbackRank
                    ]);
                    
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
            
            // Get student's best available match using consistent logic
            $targetMatch = $this->getBestAvailableMatch($student, $internshipFilter);
            
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
            
            // Get current available slots (considering both approved placements and existing endorsements)
            $currentApprovedPlacements = $internship->studentPlacements()
                ->where('status', 'approved')
                ->count();
            
            $currentEndorsements = Endorsement::where('internship_id', $internship->id)
                ->where('status', 'endorsed')
                ->count();
            
            $availableSlots = $internship->slot_count - $currentApprovedPlacements - $currentEndorsements;
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

                        // Send notification to HTE about the endorsement
                        $internshipWithHTE = Internship::with('hte.user')->find($internship->id);
                        if ($internshipWithHTE && $internshipWithHTE->hte) {
                            $notificationService = new NotificationService();
                            $studentName = $student->first_name . ' ' . $student->last_name;
                            $companyName = $internshipWithHTE->hte->company_name;
                            $notificationService->notifyHTEForEndorsement(
                                $internshipWithHTE->hte->user_id,
                                $studentName,
                                $companyName,
                                $student->id,
                                $internship->id
                            );
                        }

                        // Don't notify student yet - wait for HTE approval
                        // Student will be notified when HTE approves the endorsement
                        
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
                            $match->internship->studentPlacements()->where('status', 'approved')->count() -
                            Endorsement::where('internship_id', $match->internship->id)
                                ->where('status', 'endorsed')
                                ->count();
                        
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

                        // Send notification to HTE about the endorsement
                        $internshipWithHTE = Internship::with('hte.user')->find($fallbackInternship->id);
                        if ($internshipWithHTE && $internshipWithHTE->hte) {
                            $notificationService = new NotificationService();
                            $studentName = $student->first_name . ' ' . $student->last_name;
                            $companyName = $internshipWithHTE->hte->company_name;
                            $notificationService->notifyHTEForEndorsement(
                                $internshipWithHTE->hte->user_id,
                                $studentName,
                                $companyName,
                                $student->id,
                                $fallbackInternship->id
                            );
                        }

                        // Don't notify student yet - wait for HTE approval
                        // Student will be notified when HTE approves the endorsement
                        
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
                // But we still need to check if there are enough slots
                if ($studentsWantingThisInternship > $availableSlots) {
                    $errors[] = "Not enough slots for internship {$internship->position_title}. Available: {$availableSlots}, Requested: {$studentsWantingThisInternship}";
                    continue;
                }
                
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

                        // Send notification to HTE about the endorsement
                        $internshipWithHTE = Internship::with('hte.user')->find($internship->id);
                        if ($internshipWithHTE && $internshipWithHTE->hte) {
                            $notificationService = new NotificationService();
                            $studentName = $student->first_name . ' ' . $student->last_name;
                            $companyName = $internshipWithHTE->hte->company_name;
                            $notificationService->notifyHTEForEndorsement(
                                $internshipWithHTE->hte->user_id,
                                $studentName,
                                $companyName,
                                $student->id,
                                $internship->id
                            );
                        }

                        // Don't notify student yet - wait for HTE approval
                        // Student will be notified when HTE approves the endorsement
                        
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

    /**
     * Undo a single student endorsement
     */
    public function undoEndorsement(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'internship_id' => 'required|exists:internships,id',
        ]);

        $studentId = $validated['student_id'];
        $internshipId = $validated['internship_id'];

        try {
            // Find the endorsement to undo
            $endorsement = Endorsement::where('student_id', $studentId)
                ->where('internship_id', $internshipId)
                ->where('status', 'endorsed')
                ->first();

            if (!$endorsement) {
                return response()->json([
                    'success' => false,
                    'message' => 'No endorsed placement found to undo'
                ], 404);
            }

            // Delete the endorsement
            $endorsement->delete();

            // Update the student match status back to pending
            StudentMatch::where('student_id', $studentId)
                ->where('internship_id', $internshipId)
                ->update(['endorsement_status' => 'pending']);

            \Log::info('Endorsement undone', [
                'student_id' => $studentId,
                'internship_id' => $internshipId,
                'undone_by' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Endorsement successfully undone'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error undoing endorsement', [
                'student_id' => $studentId,
                'internship_id' => $internshipId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to undo endorsement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Undo batch endorsements
     */
    public function undoBatchEndorsements(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $studentIds = $validated['student_ids'];
        $undoneCount = 0;
        $errors = [];

        try {
            foreach ($studentIds as $studentId) {
                // Find all endorsed placements for this student
                $endorsements = Endorsement::where('student_id', $studentId)
                    ->where('status', 'endorsed')
                    ->get();

                foreach ($endorsements as $endorsement) {
                    // Delete the endorsement
                    $endorsement->delete();

                    // Update the student match status back to pending
                    StudentMatch::where('student_id', $studentId)
                        ->where('internship_id', $endorsement->internship_id)
                        ->update(['endorsement_status' => 'pending']);

                    $undoneCount++;
                }
            }

            \Log::info('Batch endorsements undone', [
                'student_ids' => $studentIds,
                'undone_count' => $undoneCount,
                'undone_by' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully undone {$undoneCount} endorsement(s)",
                'undone_count' => $undoneCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error undoing batch endorsements', [
                'student_ids' => $studentIds,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to undo batch endorsements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ordinal rank with proper suffix (1st, 2nd, 3rd, 4th, etc.)
     */
    private function getOrdinalRank(int $number): string
    {
        $value = $number % 100;
        
        // Handle special cases for 11th, 12th, 13th
        if ($value >= 11 && $value <= 13) {
            return $number . 'th';
        }
        
        // Use the last digit to determine suffix
        $lastDigit = $number % 10;
        
        switch ($lastDigit) {
            case 1:
                return $number . 'st';
            case 2:
                return $number . 'nd';
            case 3:
                return $number . 'rd';
            default:
                return $number . 'th';
        }
    }

    /**
     * Get the best available match for a student (consistent logic for both table display and batch operations)
     */
    private function getBestAvailableMatch(Student $student, ?int $internshipFilter = null): ?StudentMatch
    {
        if ($internshipFilter) {
            // If specific internship is selected, get match for that internship
            return StudentMatch::where('student_id', $student->id)
                ->where('internship_id', $internshipFilter)
                ->where('endorsement_status', 'pending')
                ->with(['internship.hte'])
                ->first();
        } else {
            // Get all matches ordered by compatibility score (highest first)
            $allMatches = StudentMatch::where('student_id', $student->id)
                ->where('endorsement_status', 'pending')
                ->with(['internship.hte'])
                ->orderBy('compatibility_score', 'desc')
                ->get();

            // Find the best available match (with slots available)
            foreach ($allMatches as $match) {
                // Calculate available slots (total slots - approved placements - endorsed slots)
                $approvedPlacements = $match->internship->studentPlacements()->where('status', 'approved')->count();
                $endorsedSlots = Endorsement::where('internship_id', $match->internship->id)
                    ->where('status', 'endorsed')
                    ->whereNotIn('status', ['cancelled'])
                    ->count();
                $availableSlots = $match->internship->slot_count - $approvedPlacements - $endorsedSlots;
                
                if ($availableSlots > 0) {
                    return $match;
                }
            }
        }

        return null;
    }


}

