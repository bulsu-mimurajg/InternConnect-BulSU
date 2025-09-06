<?php

use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AdviserController;
use App\Http\Controllers\HTEController;
use App\Models\Question;
use App\Models\SubCategory;
use App\Models\StudentMatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('about');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('contact');
})->name('contact');

// CSRF token refresh route
Route::get('/csrf-token', function () {
return response()->json(['token' => csrf_token()]);
})->middleware('web');



Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('admin-dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // HTE Management routes
    Route::get('hte', [AdminController::class, 'hteManagement'])->name('admin.hte');
    Route::get('hte/archived', [AdminController::class, 'archivedHTEManagement'])->name('admin.hte.archived');
    Route::post('hte', [AdminController::class, 'storeHTE'])->name('admin.hte.store');
    Route::put('hte/{hte}', [AdminController::class, 'updateHTE'])->name('admin.hte.update');
    Route::patch('hte/{hte}/archive', [AdminController::class, 'archiveHTE'])->name('admin.hte.archive');
    Route::patch('hte/{hte}/unarchive', [AdminController::class, 'unarchiveHTE'])->name('admin.hte.unarchive');

    // Adviser Management routes
    Route::get('adviser', [AdminController::class, 'adviserManagement'])->name('admin.adviser');
    Route::get('adviser/archived', [AdminController::class, 'archivedAdviserManagement'])->name('admin.adviser.archived');
    Route::post('adviser', [AdminController::class, 'storeAdviser'])->name('admin.adviser.store');
    Route::put('adviser/{adviser}', [AdminController::class, 'updateAdviser'])->name('admin.adviser.update');
    Route::patch('adviser/{adviser}/archive', [AdminController::class, 'archiveAdviser'])->name('admin.adviser.archive');
    Route::patch('adviser/{adviser}/unarchive', [AdminController::class, 'unarchiveAdviser'])->name('admin.adviser.unarchive');

    // Events Management routes
    Route::get('admin/events', [AdminController::class, 'eventsManagement'])->name('admin.events');
    Route::post('admin/deadlines', [AdminController::class, 'storeDeadline'])->name('admin.deadlines.store');
    Route::put('admin/deadlines/{deadline}', [AdminController::class, 'updateDeadline'])->name('admin.deadlines.update');
    Route::delete('admin/deadlines/{deadline}', [AdminController::class, 'deleteDeadline'])->name('admin.deadlines.delete');

    // Forms Management routes
    Route::get('admin/forms', [App\Http\Controllers\QuestionController::class, 'index'])->name('admin.forms');
    Route::post('admin/questions', [App\Http\Controllers\QuestionController::class, 'store'])->name('admin.questions.store');
    Route::put('admin/questions/{question}', [App\Http\Controllers\QuestionController::class, 'update'])->name('admin.questions.update');
    Route::patch('admin/questions/{question}/archive', [App\Http\Controllers\QuestionController::class, 'archive'])->name('admin.questions.archive');
    Route::patch('admin/questions/{question}/restore', [App\Http\Controllers\QuestionController::class, 'restore'])->name('admin.questions.restore');
    Route::get('admin/categories/{category}/subcategories', [App\Http\Controllers\QuestionController::class, 'getSubcategories'])->name('admin.categories.subcategories');

    Route::get('student', function () {
        return redirect()->route('student-list');
    })->name('student');
    Route::get('student/list', [StudentController::class, 'index'])->name('student-list');
    Route::get('student/unverified', [StudentController::class, 'unverified'])->name('student-unverified');

    // Specific student routes (must come before parameterized routes)
    Route::get('student/matched', [StudentController::class, 'getMatchedStudents'])->name('student-matched');
    Route::post('student/check-batch-conflicts', [StudentController::class, 'checkBatchPlacementConflicts'])->name('student.check-batch-conflicts');
    Route::post('student/batch-approve-placements', [StudentController::class, 'approveBatchPlacements'])->name('student.batch-approve-placements');
    Route::get('student/placed', [StudentController::class, 'getPlacedStudents'])->name('student-placed');

    // Parameterized student routes (must come after specific routes)
    Route::get('student/{student}/edit', [StudentController::class, 'edit'])->name('student.edit');
    Route::put('student/{student}', [StudentController::class, 'update'])->name('student.update');
    Route::patch('student/{student}/archive', [StudentController::class, 'archive'])->name('student.archive');
    Route::patch('student/{student}/restore', [StudentController::class, 'restore'])->name('student.restore');
    Route::get('student/{student}/compatibility-scores', [StudentController::class, 'getStudentCompatibilityScores'])->name('student.compatibility-scores');
    Route::get('student/{student}/details', [StudentController::class, 'getStudentDetails'])->name('student.details');
    Route::post('student/{student}/approve-placement', [StudentController::class, 'approvePlacement'])->name('student.approve-placement');
    Route::post('student/{student}/reject-placement', [StudentController::class, 'rejectPlacement'])->name('student.reject-placement');

    // Unverified user routes
    Route::get('student/unverified/{user}/edit', [StudentController::class, 'editUnverifiedUser'])->name('student.unverified.edit');
    Route::put('student/unverified/{user}', [StudentController::class, 'updateUnverifiedUser'])->name('student.unverified.update');
    Route::patch('student/unverified/{user}/archive', [StudentController::class, 'archiveUnverifiedUser'])->name('student.unverified.archive');
    Route::patch('student/unverified/{user}/restore', [StudentController::class, 'restoreUnverifiedUser'])->name('student.unverified.restore');



    Route::get('placement', function () {
        return Inertia::render('admin/placement');
    })->name('placement');

    Route::get('report', [App\Http\Controllers\AdminController::class, 'report'])->name('report');
    Route::get('report/export/pdf', [App\Http\Controllers\AdminController::class, 'exportPDF'])->name('report.export.pdf');
    Route::get('report/export/excel', [App\Http\Controllers\AdminController::class, 'exportExcel'])->name('report.export.excel');

    // Email test page
    Route::get('email-test', function () {
        return Inertia::render('admin/email-test');
    })->name('admin.email-test');
});

Route::middleware(['auth', 'verified', 'role:hte'])->group(function () {
    Route::get('form', [App\Http\Controllers\HTEController::class, 'showForm'])->name('form');
    Route::post('hte/submit', [App\Http\Controllers\HTEController::class, 'submit'])->name('hte.submit');
    Route::get('hte/categories', [App\Http\Controllers\HTEController::class, 'getCategoriesForCriteria'])->name('hte.categories');
    Route::get('hte/profile', [App\Http\Controllers\HTEController::class, 'profile'])->name('hte.profile');
    Route::get('hte/dashboard', [App\Http\Controllers\HTEController::class, 'dashboard'])->name('hte.dashboard');
    Route::get('hte/check-existing', [App\Http\Controllers\HTEController::class, 'checkExistingHTE'])->name('hte.check-existing');

    // Add Internship routes (only accessible after HTE form submission)
    Route::get('hte/add-internship', [App\Http\Controllers\HTEController::class, 'showAddInternship'])->name('hte.add-internship');
    Route::post('hte/add-internship', [App\Http\Controllers\HTEController::class, 'storeInternship'])->name('hte.store-internship');

    // Edit Internship routes
    Route::get('hte/edit-internship/{id}', [App\Http\Controllers\HTEController::class, 'showEditInternship'])->name('hte.edit-internship');
    Route::put('hte/edit-internship/{id}', [App\Http\Controllers\HTEController::class, 'updateInternship'])->name('hte.update-internship');

    // Toggle Internship Status
    Route::patch('hte/internship/{id}/toggle-status', [App\Http\Controllers\HTEController::class, 'toggleInternshipStatus'])->name('hte.toggle-internship-status');



});



Route::middleware(['auth', 'verified', 'role:adviser'])->group(function () {
    Route::get('adviser/dashboard', [AdviserController::class, 'dashboard'])->name('adviser.dashboard');
    Route::get('students', [AdviserController::class, 'getStudents'])->name('students');
    Route::get('application', [AdviserController::class, 'index'])->name('application');
    Route::post('application/approve', [AdviserController::class, 'approveStudents'])->name('application.approve');
    Route::post('application/reject', [AdviserController::class, 'rejectStudents'])->name('application.reject');
    Route::post('application/remove-access', [AdviserController::class, 'removeStudentAccess'])->name('application.remove-access');
    Route::post('application/undo', [AdviserController::class, 'undoAction'])->name('application.undo');
});

Route::group(['middleware' => ['auth', 'verified', 'role:student']], function () {
    Route::get('dashboard', function () {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return Inertia::render('student/dashboard', [
                'student' => null,
                'performance' => null,
                'possibleInternships' => [],
                'currentMatch' => null,
                'hasSubmitted' => false
            ]);
        }

        // Check if student has submitted the assessment
        if (!$student->is_submit) {
            $formattedStudent = [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'section' => $student->section->section_name ?? null,
                'specialization' => $student->specialization,
                'is_submit' => $student->is_submit,
            ];

            return Inertia::render('student/dashboard', [
                'student' => $formattedStudent,
                'performance' => null,
                'possibleInternships' => [],
                'currentMatch' => null,
                'hasSubmitted' => false
            ]);
        }

        // Get student's scores by category
        $categories = \App\Models\Category::with(['subCategories' => function ($query) use ($student) {
            $query->with(['studentScores' => function ($scoreQuery) use ($student) {
                $scoreQuery->where('student_id', $student->id);
            }]);
        }])->get();

        // Calculate overall performance metrics
        $totalScore = 0;
        $totalQuestions = 0;
        $categoryScores = [];

        foreach ($categories as $category) {
            $categoryScore = 0;
            $categoryQuestions = 0;

            foreach ($category->subCategories as $subcategory) {
                $score = $subcategory->studentScores->first();
                if ($score) {
                    $categoryScore += $score->score;
                    $categoryQuestions++;
                    $totalScore += $score->score;
                    $totalQuestions++;
                }
            }

            if ($categoryQuestions > 0) {
                $categoryScores[] = [
                    'name' => $category->category_name,
                    'average_score' => round($categoryScore / $categoryQuestions, 2),
                    'questions_count' => $categoryQuestions
                ];
            }
        }

        $overallAverage = $totalQuestions > 0 ? round($totalScore / $totalQuestions, 2) : 0;

        // Get possible internships with compatibility scores
        $matchingService = new \App\Services\MatchingService();

        // Calculate and store all compatibility scores for this student
        $matchingService->calculateAndStoreCompatibilityScores($student);

        // Get top 5 for dashboard display
        $possibleInternships = $matchingService->getTopCompatibleInternships($student, 5);

        $possibleInternships = $possibleInternships->map(function ($item) {
            $internship = $item['internship'];
            return [
                'id' => $internship->id,
                'position_title' => $internship->position_title,
                'company_name' => $internship->hte->company_name,
                'department' => $internship->department,
                'slot_count' => $internship->slot_count,
                'is_active' => $internship->is_active,
                'compatibility_score' => $item['compatibility_score'],
            ];
        });

        // Get student's current placement status
        $currentPlacement = \App\Models\StudentPlacement::where('student_id', $student->id)
            ->where('status', 'approved')
            ->with(['internship.hte:id,company_name'])
            ->first();

        $currentMatch = $currentPlacement ? [
            'id' => $currentPlacement->id,
            'internship' => [
                'position_title' => $currentPlacement->internship->position_title,
                'company_name' => $currentPlacement->internship->hte->company_name,
            ],
            'match_score' => $currentPlacement->compatibility_score ?? 0,
            'status' => $currentPlacement->status ?? 'pending',
        ] : null;

        $formattedStudent = [
            'id' => $student->id,
            'student_number' => $student->student_number,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'middle_name' => $student->middle_name,
            'section' => $student->section->section_name ?? null,
            'specialization' => $student->specialization,
            'is_submit' => $student->is_submit,
        ];

        return Inertia::render('student/dashboard', [
            'student' => $formattedStudent,
            'performance' => [
                'overall_average' => $overallAverage,
                'total_questions' => $totalQuestions,
                'category_scores' => $categoryScores,
            ],
            'possibleInternships' => $possibleInternships,
            'currentMatch' => $currentMatch,
            'hasSubmitted' => true
        ]);
    })->name('student.dashboard');
    Route::get('assessment', [AssessmentController::class, 'index'])->name('assessment');
    Route::get('student-profile', function () {
        $user = Auth::user();
        $student = $user->student;



        if (!$student) {
            return Inertia::render('student/profile', [
                'student' => null,
                'categories' => []
            ]);
        }

        // Check if student has submitted the assessment
        // Debug: Check the actual value
        \Illuminate\Support\Facades\Log::info('Profile Route Debug', [
            'student_id' => $student->id,
            'is_submit' => $student->is_submit,
            'is_submit_type' => gettype($student->is_submit),
            'has_scores' => $student->scores()->count()
        ]);

        if (!$student->is_submit) {
            $formattedStudent = [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'section' => $student->section->section_name ?? null,
                'specialization' => $student->specialization,
                'is_submit' => $student->is_submit,
            ];

            return Inertia::render('student/profile', [
                'student' => $formattedStudent,
                'categories' => [],
                'hasSubmitted' => false
            ]);
        }

        // Get all categories with their subcategories and scores
        $categories = \App\Models\Category::with(['subCategories' => function ($query) use ($student) {
            $query->with(['studentScores' => function ($scoreQuery) use ($student) {
                $scoreQuery->where('student_id', $student->id);
            }]);
        }])->get();

        // Transform the data to match frontend expectations
        $transformedCategories = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->category_name,
                'subcategories' => $category->subCategories->map(function ($subcategory) {
                    $score = $subcategory->studentScores->first();
                    return [
                        'id' => $subcategory->id,
                        'name' => $subcategory->subcategory_name,
                        'score' => $score ? round($score->score, 2) : 0,
                    ];
                })->toArray()
            ];
        })->toArray();

        $formattedStudent = [
            'id' => $student->id,
            'student_number' => $student->student_number,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'middle_name' => $student->middle_name,
            'section' => $student->section->section_name ?? null,
            'specialization' => $student->specialization,
            'is_submit' => $student->is_submit,
        ];

        return Inertia::render('student/profile', [
            'student' => $formattedStudent,
            'categories' => $transformedCategories,
            'hasSubmitted' => true
        ]);
    })->name('student-profile');

    Route::post('assessment', [AssessmentController::class, 'store'])->name('assessment.store');
    Route::get('assessment/language-proficiency', [AssessmentController::class, 'getLanguageProficiency'])->name('assessment.language-proficiency');
    Route::get('assessment/technical-skills', [AssessmentController::class, 'getTechnicalSkills'])->name('assessment.technical-skills');
    Route::get('assessment/soft-skills', [AssessmentController::class, 'getSoftSkills'])->name('assessment.soft-skills');

    // Student details route for comparison modal
    Route::get('details', function () {
        $user = Auth::user();
        $student = $user->student;

        if (!$student || !$student->is_submit) {
            return response()->json(['error' => 'Student not found or assessment not submitted'], 404);
        }

        $student->load([
            'scores.subcategory.category',
            'section'
        ]);

        // Debug: Check what's being loaded
        \Illuminate\Support\Facades\Log::info('Student Details Debug', [
            'student_id' => $student->id,
            'scores_count' => $student->scores->count(),
            'scores_with_relationships' => $student->scores->map(function($score) {
                return [
                    'score_id' => $score->id,
                    'subcategory_id' => $score->sub_category_id,
                    'score_value' => $score->score,
                    'subcategory_name' => $score->subcategory->subcategory_name ?? 'NULL',
                    'category_name' => $score->subcategory->category->name ?? 'NULL',
                ];
            })->toArray()
        ]);

        // Get the best matching internship
        $activeInternships = \App\Models\Internship::with(['hte:id,company_name', 'subcategoryWeights.subcategory.category'])
            ->where('is_active', true)
            ->where('slot_count', '>', 0)
            ->get();

        $bestMatch = null;
        $highestScore = 0;

        // Use the matching service to get all compatibility scores
        $matchingService = new \App\Services\MatchingService();
        $matchingService->calculateAndStoreCompatibilityScores($student);

        // Get the best match from stored scores
        $bestMatchData = StudentMatch::where('student_id', $student->id)
            ->with([
                'internship.hte:id,company_name',
                'internship.subcategoryWeights.subcategory.category'
            ])
            ->orderBy('compatibility_score', 'desc')
            ->first();

        if ($bestMatchData) {
            $bestMatch = [
                'internship' => $bestMatchData->internship,
                'compatibility_score' => $bestMatchData->compatibility_score,
            ];

            // Debug: Check internship criteria
            \Illuminate\Support\Facades\Log::info('Best Match Debug', [
                'internship_id' => $bestMatchData->internship->id,
                'subcategory_weights_count' => $bestMatchData->internship->subcategoryWeights->count(),
                'subcategory_weights' => $bestMatchData->internship->subcategoryWeights->map(function($weight) {
                    return [
                        'weight_id' => $weight->id,
                        'subcategory_id' => $weight->subcategory_id,
                        'weight_value' => $weight->weight,
                        'subcategory_name' => $weight->subcategory->subcategory_name ?? 'NULL',
                        'category_name' => $weight->subcategory->category->name ?? 'NULL',
                    ];
                })->toArray()
            ]);
        } else {
            // Fallback: get the best match from active internships
            foreach ($activeInternships as $internship) {
                // Calculate compatibility manually since the method is private
                $totalScore = 0;
                $totalWeight = 0;

                foreach ($internship->subcategoryWeights as $weight) {
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
                $compatibilityScore = 0;
                if ($totalWeight > 0) {
                    $compatibilityScore = round(($totalScore / $totalWeight) * 100, 2);
                }

                if ($compatibilityScore > $highestScore) {
                    $highestScore = $compatibilityScore;
                    $bestMatch = [
                        'internship' => $internship,
                        'compatibility_score' => $compatibilityScore,
                    ];
                }
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
    })->name('student.own-details');

    // Student matched route to view their compatibility scores and potential matches
    Route::get('matched', function () {
        $user = Auth::user();
        $student = $user->student;

        if (!$student || !$student->is_submit) {
            return Inertia::render('student/matched', [
                'student' => null,
                'compatibilityScores' => [],
                'currentMatch' => null,
                'hasSubmitted' => false
            ]);
        }

        // Use the matching service to get all compatibility scores
        $matchingService = new \App\Services\MatchingService();
        $matchingService->calculateAndStoreCompatibilityScores($student);

        // Get all compatibility scores for this student
        $compatibilityScores = \App\Models\StudentMatch::where('student_id', $student->id)
            ->with(['internship.hte:id,company_name', 'internship.subcategoryWeights.subcategory.category'])
            ->orderBy('compatibility_score', 'desc')
            ->get()
            ->filter(function ($match) {
                // Filter out internships with no available slots
                $availableSlots = $match->internship->slot_count -
                    $match->internship->studentPlacements()->where('status', 'approved')->count();
                return $availableSlots > 0;
            })
            ->map(function ($match) {
                return [
                    'id' => $match->id,
                    'internship' => [
                        'id' => $match->internship->id,
                        'position_title' => $match->internship->position_title,
                        'company_name' => $match->internship->hte->company_name,
                        'department' => $match->internship->department,
                        'slot_count' => $match->internship->slot_count,
                        'is_active' => $match->internship->is_active,
                    ],
                    'compatibility_score' => $match->compatibility_score,
                    'rank' => $match->rank,
                ];
            });

        // Get student's current placement status
        $currentMatch = \App\Models\StudentPlacement::where('student_id', $student->id)
            ->where('status', 'approved')
            ->with(['internship.hte:id,company_name'])
            ->first();

        $currentMatchData = $currentMatch ? [
            'id' => $currentMatch->id,
            'internship' => [
                'position_title' => $currentMatch->internship->position_title,
                'company_name' => $currentMatch->internship->hte->company_name,
            ],
            'match_score' => $currentMatch->compatibility_score ?? 0,
            'status' => $currentMatch->status ?? 'pending',
        ] : null;

        $formattedStudent = [
            'id' => $student->id,
            'student_number' => $student->student_number,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'middle_name' => $student->middle_name,
            'section' => $student->section->section_name ?? null,
            'specialization' => $student->specialization,
            'is_submit' => $student->is_submit,
        ];

        return Inertia::render('student/matched', [
            'student' => $formattedStudent,
            'compatibilityScores' => $compatibilityScores,
            'currentMatch' => $currentMatchData,
            'hasSubmitted' => true
        ]);
    })->name('student.matched');
});

Route::get('/api/categories-with-subcategories', function () {
    $categories = \App\Models\Category::with(['subCategories.questions' => function($query) {
        $query->where('is_active', true);
    }])->get();

    return response()->json($categories);
});

// Test route for dynamic sorting demonstration
Route::get('/test/sorting/{student}', function ($studentId) {
    $student = \App\Models\Student::find($studentId);
    if (!$student) {
        return response()->json(['error' => 'Student not found'], 404);
    }

    $matchingService = new \App\Services\MatchingService();

    // Test different sorting options
    $scoresByScore = $matchingService->getCompatibilityScoresSorted($student, 'compatibility_score', 'desc');
    $scoresByRank = $matchingService->getCompatibilityScoresSorted($student, 'rank', 'asc');
    $scoresByCompany = $matchingService->getCompatibilityScoresSorted($student, 'company_name', 'asc');

    return response()->json([
        'student' => [
            'id' => $student->id,
            'name' => $student->first_name . ' ' . $student->last_name,
        ],
        'sorting_examples' => [
            'by_score_desc' => $scoresByScore->take(5)->map(function($score) {
                return [
                    'company' => $score['internship']->hte->company_name,
                    'position' => $score['internship']->position_title,
                    'score' => $score['compatibility_score'],
                    'rank' => $score['rank'],
                ];
            }),
            'by_rank_asc' => $scoresByRank->take(5)->map(function($score) {
                return [
                    'company' => $score['internship']->hte->company_name,
                    'position' => $score['internship']->position_title,
                    'score' => $score['compatibility_score'],
                    'rank' => $score['rank'],
                ];
            }),
            'by_company_asc' => $scoresByCompany->take(5)->map(function($score) {
                return [
                    'company' => $score['internship']->hte->company_name,
                    'position' => $score['internship']->position_title,
                    'score' => $score['compatibility_score'],
                    'rank' => $score['rank'],
                ];
            }),
        ],
        'total_internships' => $scoresByScore->count(),
    ]);
})->name('test.sorting');


// Email routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/email/send-test', [App\Http\Controllers\EmailController::class, 'sendTestEmail'])->name('email.send-test');
    Route::post('/email/internship-notification', [App\Http\Controllers\EmailController::class, 'sendInternshipNotification'])->name('email.internship-notification');
    Route::post('/email/assessment-reminder', [App\Http\Controllers\EmailController::class, 'sendAssessmentReminder'])->name('email.assessment-reminder');
    Route::post('/email/bulk', [App\Http\Controllers\EmailController::class, 'sendBulkEmail'])->name('email.bulk');
    Route::post('/email/with-attachment', [App\Http\Controllers\EmailController::class, 'sendEmailWithAttachment'])->name('email.with-attachment');
    Route::post('/email/account-verification', [App\Http\Controllers\EmailController::class, 'sendAccountVerification'])->name('email.account-verification');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
