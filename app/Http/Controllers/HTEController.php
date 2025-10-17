<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\HTE;
use App\Models\Internship;
use App\Models\SubcategoryWeight;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\StudentMatch;
use App\Models\StudentPlacement;
use App\Models\Student;
use App\Models\Endorsement;
use App\Services\NotificationService;
use Inertia\Inertia;

class HTEController extends Controller
{
    /**
     * Check if user already has an HTE
     */
    public function checkExistingHTE()
    {
        $user = Auth::user();
        $existingHTE = $user->hte;

        return response()->json([
            'hasExistingHTE' => $existingHTE !== null,
            'hte' => $existingHTE
        ]);
    }

    /**
     * Show HTE form (always accessible, shows completion status if already submitted)
     */
    public function showForm()
    {
        $user = Auth::user();

        // Check if user has already submitted the form
        $isFormSubmitted = $user->hte && $user->hte->is_submit;

        // Check deadline status for HTE assessment form
        $deadlineActive = \App\Models\Deadline::isActiveForCategory('hte_assessment_form');
        $deadlineInfo = null;
        if (!$deadlineActive) {
            $deadlineInfo = \App\Models\Deadline::getActiveForCategory('hte_assessment_form');
        }

        // Always allow access to form, but show completion status
        return Inertia::render('hte/form', [
            'deadlineActive' => $deadlineActive,
            'deadlineInfo' => $deadlineInfo,
            'isFormSubmitted' => $isFormSubmitted,
        ]);
    }

    /**
     * Handle HTE form submission
     */
    public function submit(Request $request): RedirectResponse
    {
        // Check if HTE assessment form deadline is active
        if (!\App\Models\Deadline::isActiveForCategory('hte_assessment_form')) {
            return redirect()->back()->withErrors(['error' => 'No current Deadline or Deadline is expired. You cannot submit HTE forms at this time.']);
        }

        // Check if user has already submitted the HTE form
        $user = Auth::user();
        if ($user->hte && $user->hte->is_submit) {
            Log::warning('HTE Form Submission - User already submitted HTE form:', ['user_id' => $user->id, 'hte_id' => $user->hte->id, 'is_submit' => $user->hte->is_submit]);
            return redirect()->back()->withErrors(['error' => 'You have already submitted an HTE form. You cannot submit multiple forms.']);
        }

        // Validate the request
        try {
            $request->validate([
                'companyName' => 'required|string|max:100',
                'contactPerson' => 'required|string|max:100',
                'email' => 'required|email|max:100',
                'phone' => 'required|string|max:50',
                'address' => 'required|string|max:255',
                'position' => 'required|string|max:100',
                'department' => 'required|string|max:100',
                'numberOfInterns' => 'required|string|max:50',
                'subcategoryWeights' => 'required|array',
            ]);
            Log::info('HTE Form Submission - Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('HTE Form Submission - Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        try {
            // Create or update HTE record
            if ($user->hte) {
                // Update existing HTE record
                $hte = $user->hte;
                $hte->update([
                    'company_name' => $request->companyName,
                    'company_address' => $request->address,
                    'company_email' => $request->email,
                    'cperson_fname' => $request->contactPerson,
                    'cperson_lname' => '',
                    'cperson_position' => $request->position,
                    'cperson_contactnum' => $request->phone,
                    'is_active' => true,
                    'is_submit' => true,
                ]);
                Log::info('HTE Record Updated:', ['hte_id' => $hte->id]);
            } else {
                // Create new HTE record
                $hte = HTE::create([
                    'user_id' => $user->id,
                    'company_name' => $request->companyName,
                    'company_address' => $request->address,
                    'company_email' => $request->email,
                    'cperson_fname' => $request->contactPerson,
                    'cperson_lname' => '',
                    'cperson_position' => $request->position,
                    'cperson_contactnum' => $request->phone,
                    'is_active' => true,
                    'is_submit' => true,
                ]);
                Log::info('HTE Record Created:', ['hte_id' => $hte->id]);
            }

            // Create or update Internship record
            $existingInternship = $hte->internships()->first();
            if ($existingInternship) {
                // Update existing internship
                $internship = $existingInternship;
                $internship->update([
                    'position_title' => $request->position,
                    'department' => $request->department,
                    'placement_description' => 'Internship opportunity at ' . $request->companyName,
                    'slot_count' => (int) $request->numberOfInterns,
                    'is_active' => true,
                ]);
                Log::info('Internship Record Updated:', ['internship_id' => $internship->id]);
            } else {
                // Create new internship
                $internship = Internship::create([
                    'hte_id' => $hte->id,
                    'position_title' => $request->position,
                    'department' => $request->department,
                    'placement_description' => 'Internship opportunity at ' . $request->companyName,
                    'slot_count' => (int) $request->numberOfInterns,
                    'is_active' => true,
                ]);
                Log::info('Internship Record Created:', ['internship_id' => $internship->id]);
            }

            // Store subcategory weights (delete existing ones first if updating)
            if ($existingInternship) {
                // Delete existing weights when updating
                $internship->subcategoryWeights()->delete();
                Log::info('Deleted existing subcategory weights for internship:', ['internship_id' => $internship->id]);
            }

            $weightsCreated = 0;
            if ($request->subcategoryWeights && is_array($request->subcategoryWeights)) {
                foreach ($request->subcategoryWeights as $subcategoryId => $weight) {
                    try {
                        $subcategoryWeight = SubcategoryWeight::create([
                            'internship_id' => $internship->id,
                            'subcategory_id' => $subcategoryId,
                            'weight' => (int) $weight,
                        ]);
                        $weightsCreated++;
                        Log::info('Subcategory Weight Created:', [
                            'id' => $subcategoryWeight->id,
                            'internship_id' => $internship->id,
                            'subcategory_id' => $subcategoryId,
                            'weight' => $weight
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create subcategory weight:', [
                            'subcategory_id' => $subcategoryId,
                            'weight' => $weight,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            } else {
                Log::warning('No subcategory weights provided in request');
            }

            Log::info('Subcategory Weights Summary:', [
                'total_weights_processed' => count($request->subcategoryWeights),
                'weights_successfully_created' => $weightsCreated
            ]);

            // Recalculate student matches for new/updated internship
            try {
                $matchingService = new \App\Services\MatchingService();
                $students = \App\Models\Student::where('is_active', true)
                    ->where('is_submit', true)
                    ->get();
                
                $recalculatedCount = 0;
                foreach ($students as $student) {
                    try {
                        $matchingService->calculateAndStoreCompatibilityScores($student);
                        $recalculatedCount++;
                    } catch (\Throwable $e) {
                        Log::error('Failed to recalculate matches for student after internship creation', [
                            'student_id' => $student->id,
                            'internship_id' => $internship->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                Log::info('Recalculated student matches after internship creation/update', [
                    'internship_id' => $internship->id,
                    'recalculated_count' => $recalculatedCount,
                    'total_eligible_students' => $students->count()
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to trigger match recalculation after internship creation', [
                    'internship_id' => $internship->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('hte.dashboard')->with('success', 'HTE form submitted successfully!');

        } catch (\Exception $e) {
            Log::error('HTE Form Submission Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while submitting the form. Please try again.']);
        }
    }

    /**
     * Get categories with subcategories and questions for the criteria step
     * Excludes 'Basic Information' category
     */
    public function getCategoriesForCriteria()
    {
        // Get categories with subcategories and questions, excluding 'Basic Information'
        $categories = Category::with(['subCategories.questions' => function($query) {
            $query->where('is_active', true);
        }])
        ->where('category_name', '!=', 'Basic Information')
        ->get();

        // Transform the data to ensure proper structure for frontend
        $transformedCategories = $categories->map(function($category) {
            return [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'subCategories' => $category->subCategories->map(function($subCategory) {
                    return [
                        'id' => $subCategory->id,
                        'subcategory_name' => $subCategory->subcategory_name,
                        'category_id' => $subCategory->category_id,
                        'created_at' => $subCategory->created_at,
                        'updated_at' => $subCategory->updated_at,
                        'questions' => $subCategory->questions->map(function($question) {
                            return [
                                'id' => $question->id,
                                'question' => $question->question,
                                'is_active' => (bool) $question->is_active,
                                'subcategory_id' => $question->subcategory_id,
                                'created_at' => $question->created_at,
                                'updated_at' => $question->updated_at,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ];
        });

        // Debug: Log the structure of the first category
        if ($transformedCategories->count() > 0) {
            $firstCategory = $transformedCategories->first();
            Log::info('First category structure:', [
                'id' => $firstCategory['id'],
                'name' => $firstCategory['category_name'],
                'subCategories_count' => count($firstCategory['subCategories']),
                'subCategories_key_exists' => isset($firstCategory['subCategories']),
                'first_subcategory_name' => $firstCategory['subCategories'][0]['subcategory_name'] ?? 'N/A'
            ]);
        }

        return response()->json($transformedCategories);
    }

    /**
     * Show HTE profile page
     */
    public function profile()
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->route('form');
        }

        // Allow access to profile even if not submitted, but show prompt

        // Get HTE with related data including categories
        $hteWithData = HTE::with([
            'internships.subcategoryWeights.subcategory.category'
        ])->find($hte->id);

        // Transform the data to match frontend expectations
        $hteWithData->internships->transform(function($internship) {
            $internship->subcategory_weights = $internship->subcategoryWeights->map(function($weight) {
                return [
                    'id' => $weight->id,
                    'weight' => $weight->weight,
                    'subcategory' => [
                        'id' => $weight->subcategory->id,
                        'subcategory_name' => $weight->subcategory->subcategory_name,
                        'category' => [
                            'id' => $weight->subcategory->category->id,
                            'category_name' => $weight->subcategory->category->category_name,
                        ]
                    ]
                ];
            });
            return $internship;
        });

        // Debug: Log the data being sent to the frontend
        Log::info('HTE Profile Data:', [
            'hte_id' => $hteWithData->id,
            'internships_count' => $hteWithData->internships ? $hteWithData->internships->count() : 0,
            'first_internship_subcategory_weights_count' => $hteWithData->internships && $hteWithData->internships->first()
                ? ($hteWithData->internships->first()->subcategory_weights ? $hteWithData->internships->first()->subcategory_weights->count() : 0)
                : 0
        ]);

        // Check if HTE has complete data (submitted form OR has internships with subcategory weights)
        $hasCompleteData = $hte->is_submit ||
            ($hteWithData->internships->count() > 0 &&
             $hteWithData->internships->every(function($internship) {
                 return $internship->subcategoryWeights->count() > 0;
             }));

        // Check if there's an active student assessment deadline
        $studentAssessmentDeadlineActive = \App\Models\Deadline::isActiveForCategory('student_assessment_form');
        $studentAssessmentDeadline = null;

        if ($studentAssessmentDeadlineActive) {
            $studentAssessmentDeadline = \App\Models\Deadline::getActiveForCategory('student_assessment_form');
        }

        return Inertia::render('hte/profile', [
            'hte' => $hteWithData,
            'showSubmissionPrompt' => !$hasCompleteData,
            'studentAssessmentDeadlineActive' => $studentAssessmentDeadlineActive,
            'studentAssessmentDeadline' => $studentAssessmentDeadline ? [
                'title' => $studentAssessmentDeadline->title,
                'end_date' => $studentAssessmentDeadline->end_date->format('M d, Y H:i'),
            ] : null
        ]);
    }

    /**
     * Show HTE dashboard page
     */
    public function dashboard()
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->route('form');
        }

        // Allow access to dashboard even if not submitted, but show prompt

        // Get comprehensive dashboard data
        $dashboardData = HTE::with([
            'internships' => function($query) {
                $query->with(['subcategoryWeights.subcategory']);
            },
            'user'
        ])->find($hte->id);

        // Transform the data to match frontend expectations
        $dashboardData->internships->transform(function($internship) {
            $internship->subcategory_weights = $internship->subcategoryWeights->map(function($weight) {
                return [
                    'id' => $weight->id,
                    'weight' => $weight->weight,
                    'subcategory' => [
                        'id' => $weight->subcategory->id,
                        'subcategory_name' => $weight->subcategory->subcategory_name,
                    ]
                ];
            });

            // Add available slots information
            $availableSlots = $internship->available_slots;
            $filledSlots = $internship->filled_slots;

            $internship->available_slots_count = $availableSlots;
            $internship->filled_slots_count = $filledSlots;

            return $internship;
        });

        // Get statistics
        $totalInternships = $dashboardData->internships->count();
        $activeInternships = $dashboardData->internships->where('is_active', true)->count();
        $totalSlots = $dashboardData->internships->sum('slot_count');

        // Get internship slots breakdown for detailed view
        $internshipSlots = $dashboardData->internships->map(function($internship) {
            return [
                'id' => $internship->id,
                'position_title' => $internship->position_title,
                'slot_count' => $internship->slot_count,
                'available_slots' => $internship->available_slots_count,
                'filled_slots' => $internship->filled_slots_count,
                'is_active' => $internship->is_active,
                'created_at' => $internship->created_at
            ];
        });

        // Check if HTE has complete data (submitted form OR has internships with subcategory weights)
        $hasCompleteData = $hte->is_submit ||
            ($dashboardData->internships->count() > 0 &&
             $dashboardData->internships->every(function($internship) {
                 return $internship->subcategoryWeights->count() > 0;
             }));

        return Inertia::render('hte/dashboard', [
            'hte' => $dashboardData,
            'stats' => [
                'totalInternships' => $totalInternships,
                'activeInternships' => $activeInternships,
                'totalSlots' => $totalSlots,
                'internshipSlots' => $internshipSlots,
                'companyName' => $dashboardData->company_name,
                'contactPerson' => $dashboardData->cperson_fname . ' ' . $dashboardData->cperson_lname,
                'email' => $dashboardData->company_email,
                'phone' => $dashboardData->cperson_contactnum,
                'address' => $dashboardData->company_address,
            ],
            'showSubmissionPrompt' => !$hasCompleteData
        ]);
    }

    /**
     * Show Add Internship form (only if HTE has already submitted their form)
     */
    public function showAddInternship()
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->route('form');
        }

        // Check if there's an active student assessment deadline
        $studentAssessmentDeadlineActive = \App\Models\Deadline::isActiveForCategory('student_assessment_form');
        $studentAssessmentDeadline = null;

        if ($studentAssessmentDeadlineActive) {
            $studentAssessmentDeadline = \App\Models\Deadline::getActiveForCategory('student_assessment_form');
        }

        // Get categories for criteria selection
        $categories = Category::with(['subCategories.questions' => function($query) {
            $query->where('is_active', true);
        }])
        ->where('category_name', '!=', 'Basic Information')
        ->get();

        // Transform the data to ensure proper structure for frontend
        $transformedCategories = $categories->map(function($category) {
            return [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'subCategories' => $category->subCategories->map(function($subCategory) {
                    return [
                        'id' => $subCategory->id,
                        'subcategory_name' => $subCategory->subcategory_name,
                        'category_id' => $subCategory->category_id,
                        'created_at' => $subCategory->created_at,
                        'updated_at' => $subCategory->updated_at,
                        'questions' => $subCategory->questions->map(function($question) {
                            return [
                                'id' => $question->id,
                                'question' => $question->question,
                                'is_active' => (bool) $question->is_active,
                                'subcategory_id' => $question->subcategory_id,
                                'created_at' => $question->created_at,
                                'updated_at' => $question->updated_at,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ];
        });

        return Inertia::render('hte/add-internship', [
            'hte' => $hte,
            'categories' => $transformedCategories,
            'showSubmissionPrompt' => !$hte->is_submit,
            'studentAssessmentDeadlineActive' => $studentAssessmentDeadlineActive,
            'studentAssessmentDeadline' => $studentAssessmentDeadline ? [
                'title' => $studentAssessmentDeadline->title,
                'end_date' => $studentAssessmentDeadline->end_date->format('M d, Y H:i'),
            ] : null
        ]);
    }

    /**
     * Store new internship
     */
    public function storeInternship(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->back()->withErrors(['error' => 'You must submit an HTE form first.']);
        }

        // Check if HTE has submitted the assessment form
        if (!$hte->is_submit) {
            return redirect()->back()->withErrors(['error' => 'Please complete the assessment form first before adding internships.']);
        }

        // Check if there's an active student assessment deadline
        if (\App\Models\Deadline::isActiveForCategory('student_assessment_form')) {
            $deadline = \App\Models\Deadline::getActiveForCategory('student_assessment_form');
            return redirect()->back()->withErrors([
                'error' => "Cannot add internships during active student assessment period. Assessment period ends on " . $deadline->end_date->format('M d, Y H:i') . "."
            ]);
        }

        // Validate the request
        $request->validate([
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:50',
            'numberOfInterns' => 'required|string|max:50',
            'subcategoryWeights' => 'required|array',
        ]);

        try {
            // Create Internship record
            $internship = Internship::create([
                'hte_id' => $hte->id,
                'position_title' => $request->position,
                'department' => $request->department,
            'placement_description' => 'Internship opportunity at ' . $hte->company_name,
                'slot_count' => (int) $request->numberOfInterns,
                'is_active' => true,
            ]);

            // Store subcategory weights
            foreach ($request->subcategoryWeights as $subcategoryId => $weight) {
                SubcategoryWeight::create([
                    'internship_id' => $internship->id,
                    'subcategory_id' => $subcategoryId,
                    'weight' => (int) $weight,
                ]);
            }

            // Recalculate student matches for new internship
            try {
                $matchingService = new \App\Services\MatchingService();
                $students = \App\Models\Student::where('is_active', true)
                    ->where('is_submit', true)
                    ->get();
                
                $recalculatedCount = 0;
                foreach ($students as $student) {
                    try {
                        $matchingService->calculateAndStoreCompatibilityScores($student);
                        $recalculatedCount++;
                    } catch (\Throwable $e) {
                        Log::error('Failed to recalculate matches for student after internship creation', [
                            'student_id' => $student->id,
                            'internship_id' => $internship->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                Log::info('Recalculated student matches after internship creation', [
                    'internship_id' => $internship->id,
                    'recalculated_count' => $recalculatedCount,
                    'total_eligible_students' => $students->count()
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to trigger match recalculation after internship creation', [
                    'internship_id' => $internship->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('hte.dashboard')->with('success', 'Internship added successfully!');

        } catch (\Exception $e) {
            Log::error('Internship Creation Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the internship. Please try again.']);
        }
    }



    /**
     * Show Edit Internship form
     */
    public function showEditInternship($id)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->route('form');
        }

        // Check if HTE has submitted the assessment form
        if (!$hte->is_submit) {
            return redirect()->route('form')->with('warning', 'Please complete the assessment form first before editing internships.');
        }

        // Check if there's an active student assessment deadline
        $studentAssessmentDeadlineActive = \App\Models\Deadline::isActiveForCategory('student_assessment_form');
        $studentAssessmentDeadline = null;

        if ($studentAssessmentDeadlineActive) {
            $studentAssessmentDeadline = \App\Models\Deadline::getActiveForCategory('student_assessment_form');
        }

        // Get the internship with its weights
        $internship = Internship::with(['subcategoryWeights.subcategory.category'])
            ->where('id', $id)
            ->where('hte_id', $hte->id)
            ->first();

        if (!$internship) {
            return redirect()->route('hte.profile')->withErrors(['error' => 'Internship not found.']);
        }

        // Get categories for criteria selection
        $categories = Category::with(['subCategories.questions' => function($query) {
            $query->where('is_active', true);
        }])
        ->where('category_name', '!=', 'Basic Information')
        ->get();

        // Transform the data to ensure proper structure for frontend
        $transformedCategories = $categories->map(function($category) {
            return [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'subCategories' => $category->subCategories->map(function($subCategory) {
                    return [
                        'id' => $subCategory->id,
                        'subcategory_name' => $subCategory->subcategory_name,
                        'category_id' => $subCategory->category_id,
                        'created_at' => $subCategory->created_at,
                        'updated_at' => $subCategory->updated_at,
                        'questions' => $subCategory->questions->map(function($question) {
                            return [
                                'id' => $question->id,
                                'question' => $question->question,
                                'is_active' => (bool) $question->is_active,
                                'subcategory_id' => $question->subcategory_id,
                                'created_at' => $question->created_at,
                                'updated_at' => $question->updated_at,
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ];
        });

        // Extract internship data for editing
        $internshipData = [
            'id' => $internship->id,
            'position' => $internship->position_title,
            'department' => $internship->department,
            'numberOfInterns' => (string) $internship->slot_count,
            'duration' => $this->extractDurationFromDescription($internship->placement_description),
            'startDate' => $this->extractStartDateFromDescription($internship->placement_description),
            'endDate' => $this->extractEndDateFromDescription($internship->placement_description),
            'is_active' => $internship->is_active,
        ];

        // Extract existing weights
        $existingWeights = [];
        foreach ($internship->subcategoryWeights as $weight) {
            $existingWeights[$weight->subcategory_id] = $weight->weight;
        }

        return Inertia::render('hte/edit-internship', [
            'hte' => $hte,
            'categories' => $transformedCategories,
            'internship' => $internshipData,
            'existingWeights' => $existingWeights,
            'studentAssessmentDeadlineActive' => $studentAssessmentDeadlineActive,
            'studentAssessmentDeadline' => $studentAssessmentDeadline ? [
                'title' => $studentAssessmentDeadline->title,
                'end_date' => $studentAssessmentDeadline->end_date->format('M d, Y H:i'),
            ] : null
        ]);
    }

    /**
     * Update HTE company information
     */
    public function updateCompanyInfo(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->back()->withErrors(['error' => 'You must submit an HTE form first.']);
        }

        // Validate the request
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:500',
            'company_email' => 'required|email|max:255',
            'cperson_fname' => 'required|string|max:100',
            'cperson_lname' => 'required|string|max:100',
            'cperson_position' => 'required|string|max:100',
            'cperson_contactnum' => 'required|string|max:20',
        ]);

        try {
            // Update HTE company information
            $hte->update([
                'company_name' => $request->company_name,
                'company_address' => $request->company_address,
                'company_email' => $request->company_email,
                'cperson_fname' => $request->cperson_fname,
                'cperson_lname' => $request->cperson_lname,
                'cperson_position' => $request->cperson_position,
                'cperson_contactnum' => $request->cperson_contactnum,
            ]);

            return redirect()->back()->with('success', 'Company information updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating HTE company info: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update company information. Please try again.']);
        }
    }

    /**
     * Update existing internship
     */
    public function updateInternship(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->back()->withErrors(['error' => 'You must submit an HTE form first.']);
        }

        // Check if HTE has submitted the assessment form
        if (!$hte->is_submit) {
            return redirect()->back()->withErrors(['error' => 'Please complete the assessment form first before updating internships.']);
        }

        // Validate the request
        $request->validate([
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:50',
            'numberOfInterns' => 'required|string|max:50',
            'subcategoryWeights' => 'required|array',
        ]);

        try {
            // Get the internship
            $internship = Internship::where('id', $id)
                ->where('hte_id', $hte->id)
                ->first();

            if (!$internship) {
                return redirect()->back()->withErrors(['error' => 'Internship not found.']);
            }

            // Update Internship record
            $internship->update([
                'position_title' => $request->position,
                'department' => $request->department,
            'placement_description' => 'Internship opportunity at ' . $hte->company_name,
                'slot_count' => (int) $request->numberOfInterns,
            ]);

            // Delete existing weights and create new ones
            $internship->subcategoryWeights()->delete();

            // Store new subcategory weights
            foreach ($request->subcategoryWeights as $subcategoryId => $weight) {
                SubcategoryWeight::create([
                    'internship_id' => $internship->id,
                    'subcategory_id' => $subcategoryId,
                    'weight' => (int) $weight,
                ]);
            }

            return redirect()->route('hte.profile')->with('success', 'Internship updated successfully!');

        } catch (\Exception $e) {
            Log::error('Internship Update Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the internship. Please try again.']);
        }
    }

    /**
     * Extract duration from placement description
     */
    private function extractDurationFromDescription($description)
    {
        if (preg_match('/Duration: ([^-]+)/', $description, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    /**
     * Extract start date from placement description
     */
    private function extractStartDateFromDescription($description)
    {
        if (preg_match('/from ([^to]+) to/', $description, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    /**
     * Extract end date from placement description
     */
    private function extractEndDateFromDescription($description)
    {
        if (preg_match('/to (.+)$/', $description, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    /**
     * Toggle internship status
     */
    public function toggleInternshipStatus($id)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->back()->withErrors(['error' => 'You must submit an HTE form first.']);
        }

        // Check if HTE has submitted the assessment form
        if (!$hte->is_submit) {
            return redirect()->back()->withErrors(['error' => 'Please complete the assessment form first before managing internships.']);
        }

        // Check if there's an active student assessment deadline
        if (\App\Models\Deadline::isActiveForCategory('student_assessment_form')) {
            $deadline = \App\Models\Deadline::getActiveForCategory('student_assessment_form');
            return redirect()->back()->withErrors([
                'error' => "Cannot modify internship status during active student assessment period. Assessment period ends on " . $deadline->end_date->format('M d, Y H:i') . "."
            ]);
        }

        try {
            // Get the internship
            $internship = Internship::where('id', $id)
                ->where('hte_id', $hte->id)
                ->first();

            if (!$internship) {
                return redirect()->back()->withErrors(['error' => 'Internship not found.']);
            }

            // Toggle status
            $internship->update([
                'is_active' => !$internship->is_active
            ]);

            $status = $internship->is_active ? 'activated' : 'deactivated';
            return redirect()->back()->with('success', "Internship {$status} successfully!");

        } catch (\Exception $e) {
            Log::error('Internship Status Toggle Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the internship status. Please try again.']);
        }
    }


    /**
     * Show HTE endorsement table for endorsed students
     */
    public function showEndorsementTable(Request $request)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->route('form');
        }

        // Get HTE's internships
        $internships = $hte->internships()->active()->get();

        // Get endorsed students for this HTE's internships
        // Only show students who haven't been approved/rejected by HTE yet and are not archived
        $endorsements = Endorsement::with(['student', 'internship'])
            ->whereIn('internship_id', $internships->pluck('id'))
            ->where('status', 'endorsed')
            ->whereHas('student', function($query) {
                $query->where('is_active', true); // Only active (non-archived) students
            })
            ->get()
            ->filter(function($endorsement) {
                // Check if the student's placement status is still pending for this internship
                $studentMatch = \App\Models\StudentMatch::where('student_id', $endorsement->student_id)
                    ->where('internship_id', $endorsement->internship_id)
                    ->where('placement_status', 'pending')
                    ->first();
                return $studentMatch !== null;
            });

        // Transform endorsements for frontend
        $transformedEndorsements = $endorsements->map(function($endorsement) {
            return [
                'id' => $endorsement->id,
                'student' => [
                    'id' => $endorsement->student->id,
                    'student_number' => $endorsement->student->student_number,
                    'first_name' => $endorsement->student->first_name,
                    'last_name' => $endorsement->student->last_name,
                    'middle_name' => $endorsement->student->middle_name,
                    'specialization' => $endorsement->student->specialization,
                ],
                'internship' => [
                    'id' => $endorsement->internship->id,
                    'position' => $endorsement->internship->position_title,
                    'department' => $endorsement->internship->department,
                    'company_name' => $endorsement->internship->hte->company_name,
                    'hte_id' => $endorsement->internship->hte_id,
                ],
                'compatibility_score' => $endorsement->compatibility_score,
                'endorsement_date' => $endorsement->endorsement_date,
            ];
        });

        return Inertia::render('hte/EndorsementTable', [
            'endorsements' => $transformedEndorsements->values()->toArray(),
            'internships' => $internships->map(function($internship) {
                return [
                    'id' => $internship->id,
                    'position' => $internship->position_title,
                    'department' => $internship->department,
                    'company_name' => $internship->hte->company_name,
                    'hte_id' => $internship->hte_id,
                ];
            })->values()->toArray(),
            'hteId' => $hte->id,
            'showSubmissionPrompt' => !$hte->is_submit,
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * Show approval table for HTE to approve students based on compatibility scores
     */
    public function showApprovalTable(Request $request)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->route('form');
        }

        // Get HTE's active internships
        $internships = $hte->internships()->active()->get();

        $selectedInternshipId = $request->get('internship_id');
        $studentsForApproval = collect();

        if ($selectedInternshipId) {
            // Get students whose highest compatibility score matches this specific internship
            // Exclude archived students (is_active = false)
            $studentsForApproval = StudentMatch::with(['student', 'internship'])
                ->where('internship_id', $selectedInternshipId)
                ->whereHas('student', function($query) {
                    $query->where('is_active', true); // Only active (non-archived) students
                })
                ->where('placement_status', 'pending')
                ->get()
                ->map(function($match) {
                    // Get the rank for this student among all matches for this internship
                    $rank = StudentMatch::where('internship_id', $match->internship_id)
                        ->where('compatibility_score', '>', $match->compatibility_score)
                        ->count() + 1;

                    return [
                        'id' => $match->student->id,
                        'student_number' => $match->student->student_number,
                        'first_name' => $match->student->first_name,
                        'last_name' => $match->student->last_name,
                        'middle_name' => $match->student->middle_name,
                        'specialization' => $match->student->specialization,
                        'compatibility_score' => $match->compatibility_score,
                        'rank' => $rank,
                        'match_id' => $match->id,
                    ];
                })
                ->sortBy('rank')
                ->values();
        }

        return Inertia::render('hte/approval-table', [
            'internships' => $internships->map(function($internship) {
                return [
                    'id' => $internship->id,
                    'position_title' => $internship->position_title,
                    'department' => $internship->department,
                    'slot_count' => $internship->slot_count,
                ];
            }),
            'selectedInternshipId' => $selectedInternshipId,
            'studentsForApproval' => $studentsForApproval,
        ]);
    }

    /**
     * Approve student directly (not through endorsement system)
     */
    public function approveStudent(Request $request, $studentId)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return response()->json(['error' => 'HTE not found'], 404);
        }

        $internshipId = $request->get('internship_id');

        try {
            // Get the student match
            $studentMatch = StudentMatch::with(['student', 'internship'])
                ->where('student_id', $studentId)
                ->where('internship_id', $internshipId)
                ->where('placement_status', 'pending')
                ->whereHas('student', function($query) {
                    $query->where('is_active', true); // Ensure student is active
                })
                ->first();

            if (!$studentMatch) {
                return response()->json(['error' => 'Student match not found or student is archived'], 404);
            }

            // Check if internship belongs to this HTE
            if ($studentMatch->internship->hte_id !== $hte->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check if student already has a placement
            $existingPlacement = StudentPlacement::where('student_id', $studentId)->first();
            if ($existingPlacement) {
                return response()->json(['error' => 'Student already has a placement'], 400);
            }

            // Check if internship has available slots
            $currentApprovedPlacements = StudentPlacement::where('internship_id', $internshipId)
                ->where('status', 'approved')
                ->count();

            if ($currentApprovedPlacements >= $studentMatch->internship->slot_count) {
                return response()->json(['error' => 'No available slots for this internship'], 400);
            }

            // Create placement record
            $placement = StudentPlacement::create([
                'student_id' => $studentId,
                'internship_id' => $internshipId,
                'status' => 'approved',
                'compatibility_score' => $studentMatch->compatibility_score,
                'placement_date' => now(),
            ]);

            // Update student status
            $studentMatch->student->update(['is_placed' => true]);

            // Update the student_match record placement status to 'approved'
            $studentMatch->update(['placement_status' => 'approved']);

            // Notify student about their placement
            $notificationService = new NotificationService();
            $notificationService->notifyStudentForPlacement(
                $studentMatch->student,
                $studentMatch->internship->hte->company_name,
                $studentMatch->internship->position_title,
                $internshipId
            );

            return response()->json(['message' => 'Student approved successfully']);

        } catch (\Exception $e) {
            Log::error('HTE Student Approval Error:', [
                'error' => $e->getMessage(),
                'student_id' => $studentId,
                'internship_id' => $internshipId,
                'hte_id' => $hte->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while approving the student'], 500);
        }
    }

    /**
     * Reject student directly
     */
    public function rejectStudent(Request $request, $studentId)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return response()->json(['error' => 'HTE not found'], 404);
        }

        $internshipId = $request->get('internship_id');

        try {
            // Get the student match
            $studentMatch = StudentMatch::with(['student', 'internship'])
                ->where('student_id', $studentId)
                ->where('internship_id', $internshipId)
                ->where('placement_status', 'pending')
                ->first();

            if (!$studentMatch) {
                return response()->json(['error' => 'Student match not found'], 404);
            }

            // Check if internship belongs to this HTE
            if ($studentMatch->internship->hte_id !== $hte->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Update the student_match record placement status to 'rejected'
            $studentMatch->update(['placement_status' => 'rejected']);

            return response()->json(['message' => 'Student rejected successfully']);

        } catch (\Exception $e) {
            Log::error('HTE Student Rejection Error:', [
                'error' => $e->getMessage(),
                'student_id' => $studentId,
                'internship_id' => $internshipId,
                'hte_id' => $hte->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while rejecting the student'], 500);
        }
    }

    /**
     * Approve endorsed student
     */
    public function approveEndorsement(Request $request, $endorsementId)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return response()->json(['error' => 'HTE not found'], 404);
        }

        try {
            // Get the endorsement
            $endorsement = Endorsement::with(['student', 'internship'])
                ->where('id', $endorsementId)
                ->whereIn('internship_id', $hte->internships()->pluck('id'))
                ->first();

            if (!$endorsement) {
                return redirect()->back()->withErrors(['error' => 'Endorsement not found or access denied']);
            }

            // Check if student already has a placement
            $existingPlacement = StudentPlacement::where('student_id', $endorsement->student_id)->first();
            if ($existingPlacement) {
                return redirect()->back()->withErrors(['error' => 'Student already has a placement']);
            }

            // Check if internship has available slots
            $currentApprovedPlacements = StudentPlacement::where('internship_id', $endorsement->internship_id)
                ->where('status', 'approved')
                ->count();

            if ($currentApprovedPlacements >= $endorsement->internship->slot_count) {
                return redirect()->back()->withErrors(['error' => 'No available slots for this internship']);
            }

            // Create placement record
            $placement = StudentPlacement::create([
                'student_id' => $endorsement->student_id,
                'internship_id' => $endorsement->internship_id,
                'status' => 'approved',
                'compatibility_score' => $endorsement->compatibility_score,
                'placement_date' => now(),
            ]);

            // Update student status
            $endorsement->student->update(['is_placed' => true]);

            // Update the corresponding student_match record placement status to 'approved'
            StudentMatch::where('student_id', $endorsement->student_id)
                ->where('internship_id', $endorsement->internship_id)
                ->update(['placement_status' => 'approved']);

            // Notify student about their placement - THIS is when they should be notified!
            $notificationService = new NotificationService();
            $notificationService->notifyStudentForPlacement(
                $endorsement->student,
                $endorsement->internship->hte->company_name,
                $endorsement->internship->position_title,
                $endorsement->internship_id
            );

            // Update the endorsement status to 'approved' so it doesn't show in endorsed list anymore
            $endorsement->update(['status' => 'approved']);

//            return redirect()->back()->with('success', 'Student placement approved successfully :D');

        } catch (\Exception $e) {
            Log::error('HTE Endorsement Approval Error:', [
                'error' => $e->getMessage(),
                'endorsement_id' => $endorsementId,
                'hte_id' => $hte->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while approving the student']);
        }
    }

    /**
     * Reject endorsed student and move to next highest compatibility HTE
     */
    public function rejectEndorsement(Request $request, $endorsementId)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->back()->withErrors(['error' => 'HTE not found']);
        }

        try {
            // Get the endorsement
            $endorsement = Endorsement::with(['student', 'internship'])
                ->where('id', $endorsementId)
                ->whereIn('internship_id', $hte->internships()->pluck('id'))
                ->first();

            if (!$endorsement) {
                return redirect()->back()->withErrors(['error' => 'Endorsement not found or access denied']);
            }

            // Update endorsement status to rejected
            $endorsement->update(['status' => 'rejected']);

            // Update the corresponding student_match record placement status to 'rejected'
            StudentMatch::where('student_id', $endorsement->student_id)
                ->where('internship_id', $endorsement->internship_id)
                ->update(['placement_status' => 'rejected']);

            // Automatically endorse student to their next best match
            $fallbackResult = $this->autoEndorseToNextBestMatch($endorsement->student_id, $endorsement->internship_id);
            
            if ($fallbackResult) {
                Log::info('Student rejected by HTE, automatically endorsed to next best match:', [
                    'student_id' => $endorsement->student_id,
                    'rejected_internship_id' => $endorsement->internship_id,
                    'hte_id' => $hte->id,
                    'fallback_successful' => true,
                ]);
                
                return redirect()->back()->with('success', 'Student rejected successfully and automatically endorsed to next best match.');
            } else {
                Log::warning('Student rejected by HTE, but no fallback match found:', [
                    'student_id' => $endorsement->student_id,
                    'rejected_internship_id' => $endorsement->internship_id,
                    'hte_id' => $hte->id,
                    'fallback_successful' => false,
                ]);
                
                return redirect()->back()->with('warning', 'Student rejected successfully, but no alternative match was found.');
            }

        } catch (\Exception $e) {
            Log::error('HTE Endorsement Rejection Error:', [
                'error' => $e->getMessage(),
                'endorsement_id' => $endorsementId,
                'hte_id' => $hte->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while rejecting the student']);
        }
    }

    /**
     * Batch approve multiple endorsed students
     */
    public function batchApproveEndorsements(Request $request)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return response()->json(['error' => 'HTE not found'], 404);
        }

        $request->validate([
            'endorsement_ids' => 'required|array',
            'endorsement_ids.*' => 'integer|exists:endorsements,id'
        ]);

        $endorsementIds = $request->input('endorsement_ids');
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($endorsementIds as $endorsementId) {
            try {
                // Get the endorsement
                $endorsement = Endorsement::with(['student', 'internship'])
                    ->where('id', $endorsementId)
                    ->whereIn('internship_id', $hte->internships()->pluck('id'))
                    ->first();

                if (!$endorsement) {
                    $errors[] = "Endorsement ID {$endorsementId} not found or access denied";
                    $errorCount++;
                    continue;
                }

                // Check if student already has a placement
                $existingPlacement = StudentPlacement::where('student_id', $endorsement->student_id)->first();
                if ($existingPlacement) {
                    $errors[] = "Student {$endorsement->student->first_name} {$endorsement->student->last_name} already has a placement";
                    $errorCount++;
                    continue;
                }

                // Check if internship has available slots
                $currentApprovedPlacements = StudentPlacement::where('internship_id', $endorsement->internship_id)
                    ->where('status', 'approved')
                    ->count();

                if ($currentApprovedPlacements >= $endorsement->internship->slot_count) {
                    $errors[] = "No available slots for {$endorsement->internship->position_title} at {$endorsement->internship->hte->company_name}";
                    $errorCount++;
                    continue;
                }

                // Create placement record
                StudentPlacement::create([
                    'student_id' => $endorsement->student_id,
                    'internship_id' => $endorsement->internship_id,
                    'status' => 'approved',
                    'compatibility_score' => $endorsement->compatibility_score,
                    'placement_date' => now(),
                ]);

                // Update student status
                $endorsement->student->update(['is_placed' => true]);

                // Update the corresponding student_match record placement status to 'approved'
                StudentMatch::where('student_id', $endorsement->student_id)
                    ->where('internship_id', $endorsement->internship_id)
                    ->update(['placement_status' => 'approved']);

                // Update the endorsement status to 'approved' so it doesn't show in endorsed list anymore
                $endorsement->update(['status' => 'approved']);

                // Notify student about their placement
                $notificationService = new NotificationService();
                $notificationService->notifyStudentForPlacement(
                    $endorsement->student,
                    $endorsement->internship->hte->company_name,
                    $endorsement->internship->position_title,
                    $endorsement->internship_id
                );

                $successCount++;

            } catch (\Exception $e) {
                Log::error('HTE Batch Endorsement Approval Error:', [
                    'error' => $e->getMessage(),
                    'endorsement_id' => $endorsementId,
                    'hte_id' => $hte->id,
                    'trace' => $e->getTraceAsString()
                ]);
                $errors[] = "Error processing endorsement ID {$endorsementId}: " . $e->getMessage();
                $errorCount++;
            }
        }

        $message = "Batch approval completed: {$successCount} approved, {$errorCount} failed";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= " and " . (count($errors) - 3) . " more";
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'total_processed' => count($endorsementIds),
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ]);
    }

    /**
     * Batch reject multiple endorsed students
     */
    public function batchRejectEndorsements(Request $request)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return response()->json(['error' => 'HTE not found'], 404);
        }

        $request->validate([
            'endorsement_ids' => 'required|array',
            'endorsement_ids.*' => 'integer|exists:endorsements,id'
        ]);

        $endorsementIds = $request->input('endorsement_ids');
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($endorsementIds as $endorsementId) {
            try {
                // Get the endorsement
                $endorsement = Endorsement::with(['student', 'internship'])
                    ->where('id', $endorsementId)
                    ->whereIn('internship_id', $hte->internships()->pluck('id'))
                    ->first();

                if (!$endorsement) {
                    $errors[] = "Endorsement ID {$endorsementId} not found or access denied";
                    $errorCount++;
                    continue;
                }

                // Update endorsement status to rejected
                $endorsement->update(['status' => 'rejected']);

                // Update the corresponding student_match record placement status to 'rejected'
                StudentMatch::where('student_id', $endorsement->student_id)
                    ->where('internship_id', $endorsement->internship_id)
                    ->update(['placement_status' => 'rejected']);

                // Automatically endorse student to their next best match
                $fallbackResult = $this->autoEndorseToNextBestMatch($endorsement->student_id, $endorsement->internship_id);
                
                if ($fallbackResult) {
                    $successCount++;
                } else {
                    Log::warning('Batch rejection: No fallback match found for student:', [
                        'student_id' => $endorsement->student_id,
                        'rejected_internship_id' => $endorsement->internship_id,
                    ]);
                    $successCount++; // Still count as success since rejection worked
                }

            } catch (\Exception $e) {
                Log::error('HTE Batch Endorsement Rejection Error:', [
                    'error' => $e->getMessage(),
                    'endorsement_id' => $endorsementId,
                    'hte_id' => $hte->id,
                    'trace' => $e->getTraceAsString()
                ]);
                $errors[] = "Error processing endorsement ID {$endorsementId}: " . $e->getMessage();
                $errorCount++;
            }
        }

        $message = "Batch rejection completed: {$successCount} students rejected.";
        if ($errorCount > 0) {
            $message .= ", {$errorCount} failed";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'total_processed' => count($endorsementIds),
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ]);
    }


    /**
     * Show placed students for this HTE
     */
    public function showPlacedStudents(Request $request)
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->back()->withErrors(['error' => 'HTE not found']);
        }

        $sectionFilter = $request->get('section');
        $internshipFilter = $request->get('internship');
        $searchQuery = $request->get('search');

        // Get HTE's internships
        $internships = $hte->internships()->active()->get();

        // Base query for placed students for this HTE's internships
        $query = StudentPlacement::with(['student.section', 'internship.hte'])
            ->whereIn('internship_id', $internships->pluck('id'))
            ->where('status', 'approved') // Only show approved placements
            ->whereHas('student', function($q) {
                $q->where('is_active', true); // Only show placements for active students
            });

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

        // Get section options for filter
        $sectionOptions = StudentPlacement::with(['student.section'])
            ->whereIn('internship_id', $internships->pluck('id'))
            ->where('status', 'approved')
            ->get()
            ->groupBy('student.section.section_name')
            ->map(function ($placements, $sectionName) {
                return [
                    'name' => $sectionName,
                    'total_placements' => $placements->count(),
                ];
            })
            ->values();

        // Get internship options for filter
        $internshipOptions = $internships->map(function ($internship) {
            $placementCount = StudentPlacement::where('internship_id', $internship->id)
                ->where('status', 'approved')
                ->count();

            return [
                'id' => $internship->id,
                'position_title' => $internship->position_title,
                'department' => $internship->department,
                'total_placements' => $placementCount,
            ];
        });

        return Inertia::render('hte/PlacedStudents', [
            'placed_students' => $placedStudents,
            'section_options' => $sectionOptions,
            'internship_options' => $internshipOptions,
            'filters' => [
                'section' => $sectionFilter,
                'internship' => $internshipFilter,
                'search' => $searchQuery,
            ],
            'hteId' => $hte->id,
            'showSubmissionPrompt' => !$hte->is_submit,
        ]);
    }

    public function report()
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte) {
            return redirect()->route('form');
        }

        // Get HTE's internships for report generation
        $internships = $hte->internships()->get()->map(function($internship) {
            return [
                'id' => $internship->id,
                'position_title' => $internship->position_title,
                'department' => $internship->department,
                'slot_count' => $internship->slot_count,
                'is_active' => $internship->is_active,
            ];
        });

        return Inertia::render('hte/report', [
            'internships' => $internships,
            'showSubmissionPrompt' => !$hte->is_submit,
        ]);
    }

    /**
     * Generate general HTE report (PDF)
     */
    public function generateGeneralReportPdf(Request $request, $reportType): \Illuminate\Http\Response
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte || !$hte->is_submit) {
            abort(403, 'Access denied. Please complete your assessment form first.');
        }

        // Validate report type
        $validReportTypes = [
            'company-overview',
            'placed-students',
            'internship-slots'
        ];

        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        // Get report data based on type
        $reportData = $this->getHTEGeneralReportData($hte, $reportType);

        // Generate HTML content for PDF
        $html = view("reports.hte-{$reportType}", array_merge($reportData, [
            'hte' => $hte,
            'generatedAt' => now()->format('F d, Y \a\t h:i A'),
        ]))->render();

        // Generate PDF using DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Return PDF download
        $filename = $this->generateFilename($hte, $reportType, 'pdf');
        return $pdf->download($filename);
    }

    /**
     * Generate general HTE report (Excel/CSV)
     */
    public function generateGeneralReportExcel(Request $request, $reportType): \Illuminate\Http\Response
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte || !$hte->is_submit) {
            abort(403, 'Access denied. Please complete your assessment form first.');
        }

        // Validate report type
        $validReportTypes = [
            'company-overview',
            'placed-students',
            'internship-slots'
        ];

        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        $csvContent = $this->generateHTEGeneralCSVContent($hte, $reportType);
        $filename = $this->generateFilename($hte, $reportType, 'csv');

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate internship-specific HTE report (PDF)
     */
    public function generateInternshipReportPdf(Request $request, $internshipId, $reportType): \Illuminate\Http\Response
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte || !$hte->is_submit) {
            abort(403, 'Access denied. Please complete your assessment form first.');
        }

        // Validate that the internship belongs to this HTE
        $internship = $hte->internships()->find($internshipId);
        if (!$internship) {
            abort(404, 'Internship not found or access denied.');
        }

        // Validate report type
        $validReportTypes = [
            'internship-performance',
            'student-compatibility'
        ];

        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        // Get report data based on type
        $reportData = $this->getHTEInternshipReportData($hte, $internship, $reportType);

        // Generate HTML content for PDF
        $html = view("reports.hte-internship-{$reportType}", array_merge($reportData, [
            'hte' => $hte,
            'internship' => $internship,
            'generatedAt' => now()->format('F d, Y \a\t h:i A'),
        ]))->render();

        // Generate PDF using DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        // Return PDF download
        $filename = $this->generateFilename($hte, $reportType, 'pdf', $internship);
        return $pdf->download($filename);
    }

    /**
     * Generate internship-specific HTE report (Excel/CSV)
     */
    public function generateInternshipReportExcel(Request $request, $internshipId, $reportType): \Illuminate\Http\Response
    {
        $user = Auth::user();
        $hte = $user->hte;

        if (!$hte || !$hte->is_submit) {
            abort(403, 'Access denied. Please complete your assessment form first.');
        }

        // Validate that the internship belongs to this HTE
        $internship = $hte->internships()->find($internshipId);
        if (!$internship) {
            abort(404, 'Internship not found or access denied.');
        }

        // Validate report type
        $validReportTypes = [
            'internship-performance',
            'student-compatibility'
        ];

        if (!in_array($reportType, $validReportTypes)) {
            abort(404, 'Invalid report type.');
        }

        $csvContent = $this->generateHTEInternshipCSVContent($hte, $internship, $reportType);
        $filename = $this->generateFilename($hte, $reportType, 'csv', $internship);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get HTE general report data
     */
    private function getHTEGeneralReportData($hte, $reportType): array
    {
        switch ($reportType) {
            case 'company-overview':
                return $this->getHTECompanyOverviewData($hte);
            case 'placed-students':
                return $this->getHTEPlacedStudentsData($hte);
            case 'internship-slots':
                return $this->getHTEInternshipSlotsData($hte);
            default:
                return [];
        }
    }

    /**
     * Get HTE internship report data
     */
    private function getHTEInternshipReportData($hte, $internship, $reportType): array
    {
        switch ($reportType) {
            case 'internship-performance':
                return $this->getHTEInternshipPerformanceData($hte, $internship);
            case 'student-compatibility':
                return $this->getHTEStudentCompatibilityData($hte, $internship);
            default:
                return [];
        }
    }

    /**
     * Generate filename for the report
     */
    private function generateFilename($hte, $reportType, $format, $internship = null)
    {
        $companyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $hte->company_name);
        $reportTypeName = str_replace('-', '_', $reportType);
        $date = now()->format('Y-m-d');

        $filename = "HTE_{$companyName}_{$reportTypeName}_{$date}";

        if ($internship) {
            $internshipName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $internship->position_title);
            $filename .= "_{$internshipName}";
        }

        $filename .= ".{$format}";

        return $filename;
    }

    // Data generation methods for HTE reports
    private function getHTECompanyOverviewData($hte): array
    {
        $internships = $hte->internships()->with('subcategoryWeights.subcategory.category')->get();
        $totalSlots = $internships->sum('slot_count');
        $activeInternships = $internships->where('is_active', true)->count();

        $placedStudents = StudentPlacement::whereHas('internship', function($query) use ($hte) {
            $query->where('hte_id', $hte->id);
        })->where('status', 'approved')->count();

        $utilizationRate = $totalSlots > 0 ? round(($placedStudents / $totalSlots) * 100, 1) : 0;

        return [
            'internships' => $internships,
            'totalSlots' => $totalSlots,
            'activeInternships' => $activeInternships,
            'placedStudents' => $placedStudents,
            'utilizationRate' => $utilizationRate,
        ];
    }

    private function getHTEPlacedStudentsData($hte): array
    {
        $placedStudents = StudentPlacement::whereHas('internship', function($query) use ($hte) {
            $query->where('hte_id', $hte->id);
        })
        ->with(['student.section', 'internship'])
        ->where('status', 'approved')
        ->get()
        ->map(function($placement) {
            return [
                'student_number' => $placement->student->student_number,
                'name' => $placement->student->first_name . ' ' . $placement->student->last_name,
                'section' => $placement->student->section->section_name ?? 'N/A',
                'position' => $placement->internship->position_title,
                'department' => $placement->internship->department,
                'compatibility_score' => $placement->compatibility_score,
                'placement_date' => $placement->placement_date ? $placement->placement_date->format('Y-m-d') : 'N/A',
            ];
        });

        return ['placedStudents' => $placedStudents];
    }

    private function getHTEInternshipSlotsData($hte): array
    {
        $internships = $hte->internships()->get()->map(function($internship) {
            $filledSlots = StudentPlacement::where('internship_id', $internship->id)
                ->where('status', 'approved')
                ->count();

            $utilizationRate = $internship->slot_count > 0 ?
                round(($filledSlots / $internship->slot_count) * 100, 1) : 0;

            return [
                'id' => $internship->id,
                'position_title' => $internship->position_title,
                'department' => $internship->department,
                'slot_count' => $internship->slot_count,
                'filled_slots' => $filledSlots,
                'available_slots' => $internship->slot_count - $filledSlots,
                'utilization_rate' => $utilizationRate,
                'is_active' => $internship->is_active,
            ];
        });

        return ['internships' => $internships];
    }

    private function getHTEPlacementTimelineData($hte): array
    {
        $placements = StudentPlacement::whereHas('internship', function($query) use ($hte) {
            $query->where('hte_id', $hte->id);
        })
        ->with(['student.section', 'internship'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($placement) {
            return [
                'student_name' => $placement->student->first_name . ' ' . $placement->student->last_name,
                'position' => $placement->internship->position_title,
                'status' => $placement->status,
                'compatibility_score' => $placement->compatibility_score,
                'created_at' => $placement->created_at->format('Y-m-d H:i:s'),
                'placement_date' => $placement->placement_date ? $placement->placement_date->format('Y-m-d') : 'N/A',
            ];
        });

        return ['placements' => $placements];
    }

    private function getHTEEndorsementSummaryData($hte): array
    {
        $endorsements = Endorsement::whereHas('internship', function($query) use ($hte) {
            $query->where('hte_id', $hte->id);
        })
        ->with(['student.section', 'internship'])
        ->get()
        ->groupBy('status')
        ->map(function($group, $status) {
            return $group->map(function($endorsement) {
                return [
                    'student_name' => $endorsement->student->first_name . ' ' . $endorsement->student->last_name,
                    'position' => $endorsement->internship->position_title,
                    'compatibility_score' => $endorsement->compatibility_score,
                    'endorsement_date' => $endorsement->endorsement_date ? $endorsement->endorsement_date->format('Y-m-d') : 'N/A',
                ];
            });
        });

        return ['endorsements' => $endorsements];
    }

    private function getHTEInternshipPerformanceData($hte, $internship): array
    {
        $placements = StudentPlacement::where('internship_id', $internship->id)
            ->with(['student.section'])
            ->get()
            ->map(function($placement) {
                return [
                    'student_name' => $placement->student->first_name . ' ' . $placement->student->last_name,
                    'section' => $placement->student->section->section_name ?? 'N/A',
                    'compatibility_score' => $placement->compatibility_score,
                    'status' => $placement->status,
                    'placement_date' => $placement->placement_date ? $placement->placement_date->format('Y-m-d') : 'N/A',
                ];
            });

        $avgCompatibilityScore = $placements->avg('compatibility_score') ?? 0;
        $filledSlots = $placements->where('status', 'approved')->count();
        $utilizationRate = $internship->slot_count > 0 ?
            round(($filledSlots / $internship->slot_count) * 100, 1) : 0;

        return [
            'placements' => $placements,
            'avgCompatibilityScore' => round($avgCompatibilityScore, 2),
            'filledSlots' => $filledSlots,
            'utilizationRate' => $utilizationRate,
        ];
    }

    private function getHTEStudentCompatibilityData($hte, $internship): array
    {
        $placements = StudentPlacement::where('internship_id', $internship->id)
            ->with(['student.section'])
            ->orderBy('compatibility_score', 'desc')
            ->get()
            ->map(function($placement) {
                return [
                    'student_name' => $placement->student->first_name . ' ' . $placement->student->last_name,
                    'section' => $placement->student->section->section_name ?? 'N/A',
                    'compatibility_score' => $placement->compatibility_score,
                    'status' => $placement->status,
                ];
            });

        return ['placements' => $placements];
    }

    private function getHTECriteriaWeightsData($hte, $internship): array
    {
        $weights = $internship->subcategoryWeights()
            ->with('subcategory.category')
            ->get()
            ->map(function($weight) {
                return [
                    'category' => $weight->subcategory->category->category_name,
                    'subcategory' => $weight->subcategory->subcategory_name,
                    'weight' => $weight->weight,
                ];
            });

        return ['weights' => $weights];
    }

    // CSV generation methods
    private function generateHTEGeneralCSVContent($hte, $reportType): string
    {
        $csvContent = ucwords(str_replace('-', ' ', $reportType)) . " report - {$hte->company_name}\n";
        $csvContent .= "Generated: " . now()->format('F d, Y \a\t h:i A') . "\n\n";

        switch ($reportType) {
            case 'company-overview':
                return $this->generateHTECompanyOverviewCSV($hte, $csvContent);
            case 'placed-students':
                return $this->generateHTEPlacedStudentsCSV($hte, $csvContent);
            case 'internship-slots':
                return $this->generateHTEInternshipSlotsCSV($hte, $csvContent);
            default:
                return $csvContent;
        }
    }

    private function generateHTEInternshipCSVContent($hte, $internship, $reportType): string
    {
        $csvContent = ucwords(str_replace('-', ' ', $reportType)) . " report - {$hte->company_name}\n";
        $csvContent .= "Internship: {$internship->position_title}\n";
        $csvContent .= "Generated: " . now()->format('F d, Y \a\t h:i A') . "\n\n";

        switch ($reportType) {
            case 'internship-performance':
                return $this->generateHTEInternshipPerformanceCSV($hte, $internship, $csvContent);
            case 'student-compatibility':
                return $this->generateHTEStudentCompatibilityCSV($hte, $internship, $csvContent);
            default:
                return $csvContent;
        }
    }

    // CSV generation helper methods
    private function generateHTECompanyOverviewCSV($hte, $csvContent): string
    {
        $data = $this->getHTECompanyOverviewData($hte);

        $csvContent .= "Company Overview\n";
        $csvContent .= "Total Internships," . $data['internships']->count() . "\n";
        $csvContent .= "Active Internships," . $data['activeInternships'] . "\n";
        $csvContent .= "Total Slots," . $data['totalSlots'] . "\n";
        $csvContent .= "Placed Students," . $data['placedStudents'] . "\n";
        $csvContent .= "Utilization Rate," . $data['utilizationRate'] . "%\n\n";

        $csvContent .= "Internship Details\n";
        $csvContent .= "Position,Department,Slots,Active Status\n";
        foreach ($data['internships'] as $internship) {
            $csvContent .= '"' . $internship->position_title . '",';
            $csvContent .= '"' . $internship->department . '",';
            $csvContent .= $internship->slot_count . ",";
            $csvContent .= ($internship->is_active ? 'Yes' : 'No') . "\n";
        }

        return $csvContent;
    }

    private function generateHTEPlacedStudentsCSV($hte, $csvContent): string
    {
        $data = $this->getHTEPlacedStudentsData($hte);

        $csvContent .= "Placed Students\n";
        $csvContent .= "Student Number,Name,Section,Position,Department,Compatibility Score,Placement Date\n";

        foreach ($data['placedStudents'] as $student) {
            $csvContent .= $student['student_number'] . ",";
            $csvContent .= '"' . $student['name'] . '",';
            $csvContent .= $student['section'] . ",";
            $csvContent .= '"' . $student['position'] . '",';
            $csvContent .= '"' . $student['department'] . '",';
            $csvContent .= $student['compatibility_score'] . ",";
            $csvContent .= $student['placement_date'] . "\n";
        }

        return $csvContent;
    }

    private function generateHTEInternshipSlotsCSV($hte, $csvContent): string
    {
        $data = $this->getHTEInternshipSlotsData($hte);

        $csvContent .= "Internship Slots Utilization\n";
        $csvContent .= "Position,Department,Total Slots,Filled Slots,Available Slots,Utilization Rate,Status\n";

        foreach ($data['internships'] as $internship) {
            $csvContent .= '"' . $internship['position_title'] . '",';
            $csvContent .= '"' . $internship['department'] . '",';
            $csvContent .= $internship['slot_count'] . ",";
            $csvContent .= $internship['filled_slots'] . ",";
            $csvContent .= $internship['available_slots'] . ",";
            $csvContent .= $internship['utilization_rate'] . "%,";
            $csvContent .= ($internship['is_active'] ? 'Active' : 'Inactive') . "\n";
        }

        return $csvContent;
    }

    private function generateHTEPlacementTimelineCSV($hte, $csvContent): string
    {
        $data = $this->getHTEPlacementTimelineData($hte);

        $csvContent .= "Placement Timeline\n";
        $csvContent .= "Student Name,Position,Status,Compatibility Score,Created At,Placement Date\n";

        foreach ($data['placements'] as $placement) {
            $csvContent .= '"' . $placement['student_name'] . '",';
            $csvContent .= '"' . $placement['position'] . '",';
            $csvContent .= ucfirst($placement['status']) . ",";
            $csvContent .= $placement['compatibility_score'] . ",";
            $csvContent .= $placement['created_at'] . ",";
            $csvContent .= $placement['placement_date'] . "\n";
        }

        return $csvContent;
    }

    private function generateHTEEndorsementSummaryCSV($hte, $csvContent): string
    {
        $data = $this->getHTEEndorsementSummaryData($hte);

        $csvContent .= "Endorsement Summary\n";
        $csvContent .= "Status,Student Name,Position,Compatibility Score,Endorsement Date\n";

        foreach ($data['endorsements'] as $status => $endorsements) {
            foreach ($endorsements as $endorsement) {
                $csvContent .= ucfirst($status) . ",";
                $csvContent .= '"' . $endorsement['student_name'] . '",';
                $csvContent .= '"' . $endorsement['position'] . '",';
                $csvContent .= $endorsement['compatibility_score'] . ",";
                $csvContent .= $endorsement['endorsement_date'] . "\n";
            }
        }

        return $csvContent;
    }

    private function generateHTEInternshipPerformanceCSV($hte, $internship, $csvContent): string
    {
        $data = $this->getHTEInternshipPerformanceData($hte, $internship);

        $csvContent .= "Performance Summary\n";
        $csvContent .= "Average Compatibility Score," . $data['avgCompatibilityScore'] . "\n";
        $csvContent .= "Filled Slots," . $data['filledSlots'] . "\n";
        $csvContent .= "Utilization Rate," . $data['utilizationRate'] . "%\n\n";

        $csvContent .= "Student Placements\n";
        $csvContent .= "Student Name,Section,Compatibility Score,Status,Placement Date\n";

        foreach ($data['placements'] as $placement) {
            $csvContent .= '"' . $placement['student_name'] . '",';
            $csvContent .= $placement['section'] . ",";
            $csvContent .= $placement['compatibility_score'] . ",";
            $csvContent .= ucfirst($placement['status']) . ",";
            $csvContent .= $placement['placement_date'] . "\n";
        }

        return $csvContent;
    }

    private function generateHTEStudentCompatibilityCSV($hte, $internship, $csvContent): string
    {
        $data = $this->getHTEStudentCompatibilityData($hte, $internship);

        $csvContent .= "Student Compatibility Rankings\n";
        $csvContent .= "Rank,Student Name,Section,Compatibility Score,Status\n";

        foreach ($data['placements'] as $index => $placement) {
            $csvContent .= ($index + 1) . ",";
            $csvContent .= '"' . $placement['student_name'] . '",';
            $csvContent .= $placement['section'] . ",";
            $csvContent .= $placement['compatibility_score'] . ",";
            $csvContent .= ucfirst($placement['status']) . "\n";
        }

        return $csvContent;
    }

    private function generateHTECriteriaWeightsCSV($hte, $internship, $csvContent): string
    {
        $data = $this->getHTECriteriaWeightsData($hte, $internship);

        $csvContent .= "Criteria Weights\n";
        $csvContent .= "Category,Subcategory,Weight\n";

        foreach ($data['weights'] as $weight) {
            $csvContent .= $weight['category'] . ",";
            $csvContent .= '"' . $weight['subcategory'] . '",';
            $csvContent .= $weight['weight'] . "\n";
        }

        return $csvContent;
    }

    /**
     * Automatically endorse student to their next best match after HTE rejection
     */
    private function autoEndorseToNextBestMatch($studentId, $rejectedInternshipId)
    {
        try {
            Log::info('Starting auto-endorsement process:', [
                'student_id' => $studentId,
                'rejected_internship_id' => $rejectedInternshipId,
            ]);

            // Find the student's next highest compatibility match
            // Look for pending endorsement matches (not yet endorsed by admin)
            $nextMatch = StudentMatch::with(['internship.hte'])
                ->where('student_id', $studentId)
                ->where('endorsement_status', 'pending')
                ->where('internship_id', '!=', $rejectedInternshipId) // Exclude the rejected internship
                ->orderBy('compatibility_score', 'desc')
                ->first();

            Log::info('Next match query result:', [
                'student_id' => $studentId,
                'next_match_found' => $nextMatch ? true : false,
                'next_match_id' => $nextMatch ? $nextMatch->internship_id : null,
                'compatibility_score' => $nextMatch ? $nextMatch->compatibility_score : null,
            ]);

            if ($nextMatch) {
                // Check if the next match has available slots
                $approvedPlacements = $nextMatch->internship->studentPlacements()->where('status', 'approved')->count();
                $endorsedSlots = Endorsement::where('internship_id', $nextMatch->internship_id)
                    ->where('status', 'endorsed')
                    ->count();
                $availableSlots = $nextMatch->internship->slot_count - $approvedPlacements - $endorsedSlots;

                Log::info('Slot availability check:', [
                    'student_id' => $studentId,
                    'internship_id' => $nextMatch->internship_id,
                    'total_slots' => $nextMatch->internship->slot_count,
                    'approved_placements' => $approvedPlacements,
                    'endorsed_slots' => $endorsedSlots,
                    'available_slots' => $availableSlots,
                ]);

                if ($availableSlots > 0) {
                    // Update the student_match record endorsement status to 'endorsed'
                    StudentMatch::where('student_id', $studentId)
                        ->where('internship_id', $nextMatch->internship_id)
                        ->update(['endorsement_status' => 'endorsed']);

                    // Create endorsement record
                    Endorsement::create([
                        'student_id' => $studentId,
                        'internship_id' => $nextMatch->internship_id,
                        'status' => 'endorsed',
                        'compatibility_score' => $nextMatch->compatibility_score,
                        'endorsement_date' => now(),
                    ]);

                    // Send notification to HTE about the new endorsement
                    $internship = $nextMatch->internship;
                    if ($internship && $internship->hte) {
                        $notificationService = new \App\Services\NotificationService();
                        $student = \App\Models\Student::find($studentId);
                        $studentName = $student->first_name . ' ' . $student->last_name;
                        $companyName = $internship->hte->company_name;
                        $notificationService->notifyHTEForEndorsement(
                            $internship->hte->user_id,
                            $studentName,
                            $companyName,
                            $studentId,
                            $internship->id
                        );
                    }

                    Log::info('Student automatically endorsed to next best match:', [
                        'student_id' => $studentId,
                        'new_internship_id' => $nextMatch->internship_id,
                        'compatibility_score' => $nextMatch->compatibility_score,
                        'hte_id' => $nextMatch->internship->hte_id,
                    ]);

                    return true;
                } else {
                    Log::warning('Next best match has no available slots:', [
                        'student_id' => $studentId,
                        'internship_id' => $nextMatch->internship_id,
                        'available_slots' => $availableSlots,
                    ]);
                }
            } else {
                Log::warning('No next best match found for student:', [
                    'student_id' => $studentId,
                    'rejected_internship_id' => $rejectedInternshipId,
                ]);
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Auto-endorsement to next best match failed:', [
                'student_id' => $studentId,
                'rejected_internship_id' => $rejectedInternshipId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
