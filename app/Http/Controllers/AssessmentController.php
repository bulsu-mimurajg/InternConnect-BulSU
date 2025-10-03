<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Question; // Added this import
use App\Models\StudentScore;
use App\Models\Internship; // Added this import
use App\Models\AdditionalInfo;
use App\Models\StudentAdditionalInfo;
use App\Services\MatchingService;
use App\Services\NotificationService;
use Inertia\Inertia;

class AssessmentController extends Controller
{
    /**
     * Handle assessment form submission
     */
    public function index()
    {
        // Check if the authenticated user's student record has already submitted the assessment
        $student = Auth::user()->student;
        $hasSubmitted = $student ? $student->is_submit : false;

        // Check deadline status for student assessment form
        $deadlineActive = \App\Models\Deadline::isActiveForCategory('student_assessment_form');
        $deadlineInfo = null;
        if (!$deadlineActive) {
            $deadlineInfo = \App\Models\Deadline::getActiveForCategory('student_assessment_form');
        }

        // Get active additional info fields
        $additionalInfos = AdditionalInfo::where('is_active', true)
            ->orderBy('info_name')
            ->get();

        return Inertia::render('student/assessment', [
            'hasSubmitted' => $hasSubmitted,
            'deadlineActive' => $deadlineActive,
            'deadlineInfo' => $deadlineInfo,
            'additionalInfos' => $additionalInfos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Log the incoming request for debugging
        \Log::info('Assessment submission attempt', [
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'has_csrf_token' => $request->has('_token'),
        ]);

        // Check if student assessment form deadline is active
        if (!\App\Models\Deadline::isActiveForCategory('student_assessment_form')) {
            return redirect()->back()->withErrors(['error' => 'No current Deadline or Deadline is expired. You cannot submit assessments at this time.']);
        }

        // Get all questions from database to build dynamic validation rules
        $questions = Question::where('is_active', true)->get();
        $additionalInfos = AdditionalInfo::where('is_active', true)->get();

        // Initialize validation rules array
        $validationRules = [];

        // Add validation rules for each question
        foreach ($questions as $question) {
            $subcategory = $question->subcategory;
            $fieldName = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subcategory->subcategory_name)) . '_' . $question->id;
            $validationRules[$fieldName] = 'required|integer|min:1|max:5';
        }

        // Add validation rules for additional info fields
        foreach ($additionalInfos as $additionalInfo) {
            $fieldName = strtolower(str_replace([' ', '-'], ['_', '_'], $additionalInfo->info_name));
            $validationRules[$fieldName] = 'required|string|max:255';
        }

        // If no validation rules are set, add a dummy rule to prevent empty validation
        if (empty($validationRules)) {
            $validationRules['dummy'] = 'nullable|string';
        }

        // Remove dummy field from request data if it exists
        if ($request->has('dummy')) {
            $request->request->remove('dummy');
        }

        // Validate the request
        try {
            $request->validate($validationRules);
            \Log::info('Assessment validation passed', [
                'validation_rules_count' => count($validationRules),
                'request_fields_count' => count($request->all())
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Assessment validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'validation_rules' => $validationRules
            ]);
            throw $e;
        }

        try {
            // Get the authenticated user
            $user = Auth::user();
            \Log::info('Processing assessment for user', ['user_id' => $user->id]);

            // Find student record
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                \Log::error('Student record not found for user', ['user_id' => $user->id]);
                return redirect()->back()->withErrors(['error' => 'Student profile not found. Please contact administrator.']);
            } else {
                \Log::info('Found existing student record', ['student_id' => $student->id]);
            }

            // Store assessment data in session for now (or you can store in a different way)
            $assessmentData = [
                'student_id' => $student->id,
                'additional_info' => [],
                'questions' => [],
                'submitted_at' => now(),
            ];

            // Store question responses and compute scores
            $subcategoryScores = [];
            \Log::info('Processing questions', ['questions_count' => $questions->count()]);

            foreach ($questions as $question) {
                $subcategory = $question->subcategory;
                $fieldName = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subcategory->subcategory_name)) . '_' . $question->id;

                if ($request->has($fieldName)) {
                    $response = $request->input($fieldName);

                    // Store in assessment data
                    $assessmentData['questions'][] = [
                        'question_id' => $question->id,
                        'subcategory_id' => $subcategory->id,
                        'category_id' => $subcategory->category_id,
                        'response' => $response,
                        'question_text' => $question->question,
                        'subcategory_name' => $subcategory->subcategory_name,
                        'category_name' => $subcategory->category->category_name,
                    ];

                    // Collect scores for mean calculation
                    if (!isset($subcategoryScores[$subcategory->id])) {
                        $subcategoryScores[$subcategory->id] = [];
                    }
                    $subcategoryScores[$subcategory->id][] = $response;
                }
            }

            // Compute mean scores for each subcategory and store in student_score table
            \Log::info('Computing scores', ['subcategory_count' => count($subcategoryScores)]);
            foreach ($subcategoryScores as $subcategoryId => $scores) {
                $meanScore = (array_sum($scores) / count($scores));

                // Update or create student score record
                StudentScore::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'sub_category_id' => $subcategoryId,
                    ],
                    [
                        'score' => $meanScore,
                    ]
                );
            }

            // Store additional info data
            \Log::info('Processing additional info', ['additional_info_count' => $additionalInfos->count()]);
            foreach ($additionalInfos as $additionalInfo) {
                $fieldName = strtolower(str_replace([' ', '-'], ['_', '_'], $additionalInfo->info_name));
                
                if ($request->has($fieldName)) {
                    $infoValue = $request->input($fieldName);
                    
                    // Store in database
                    StudentAdditionalInfo::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'additional_info_id' => $additionalInfo->id,
                        ],
                        [
                            'info' => $infoValue,
                        ]
                    );
                    
                    // Add to assessment data
                    $assessmentData['additional_info'][] = [
                        'info_name' => $additionalInfo->info_name,
                        'info_value' => $infoValue,
                    ];
                }
            }

            // Update student's is_submit status to true
            \Log::info('Updating student submission status', ['student_id' => $student->id]);
            $student->update(['is_submit' => true]);

            // Notify admins that student has completed assessment and is waiting for endorsement
            $this->notifyAdminsForStudentAssessmentCompletion($student);

            // Store in session for now (you can modify this to store in database later)
            session(['assessment_data' => $assessmentData]);

            \Log::info('Assessment submitted successfully', ['student_id' => $student->id]);
            return redirect()->route('assessment')->with('success', 'Assessment submitted successfully!');

        } catch (\Exception $e) {
            \Log::error('Assessment submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'Failed to submit assessment. Please try again.');
        }
    }

    /**
     * Get language proficiency data from database
     */
    public function getLanguageProficiency()
    {
        try {
            $languageCategory = Category::where('category_name', 'Language Proficiency')->first();

            if (!$languageCategory) {
                return response()->json(['error' => 'Language Proficiency category not found'], 404);
            }

            $subCategories = SubCategory::with(['questions' => function ($query) {
                $query->where('is_active', true);
            }])->where('category_id', $languageCategory->id)->get();

            $languageProficiencySections = [];

            foreach ($subCategories as $subCategory) {
                if ($subCategory->questions->count() > 0) {
                    $sectionSkills = [];
                    foreach ($subCategory->questions as $question) {
                        $sectionSkills[] = [
                            'name' => strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subCategory->subcategory_name)) . '_' . $question->id,
                            'label' => $question->question,
                            'subcategory_id' => $subCategory->id,
                            'question_id' => $question->id
                        ];
                    }

                    $languageProficiencySections[] = [
                        'title' => $subCategory->subcategory_name,
                        'skills' => $sectionSkills
                    ];
                }
            }

            return response()->json($languageProficiencySections);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve language proficiency data'], 500);
        }
    }

    /**
     * Get technical skills data from database
     */
    public function getTechnicalSkills()
    {
        try {
            $technicalCategory = Category::where('category_name', 'Technical Skill')->first();

            if (!$technicalCategory) {
                return response()->json(['error' => 'Technical Skill category not found'], 404);
            }

            $subCategories = SubCategory::with(['questions' => function ($query) {
                $query->where('is_active', true);
            }])->where('category_id', $technicalCategory->id)->get();

            $technicalSkillSections = [];

            foreach ($subCategories as $subCategory) {
                if ($subCategory->questions->count() > 0) {
                    $sectionSkills = [];
                    foreach ($subCategory->questions as $question) {
                        $sectionSkills[] = [
                            'name' => strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subCategory->subcategory_name)) . '_' . $question->id,
                            'label' => $question->question,
                            'subcategory_id' => $subCategory->id,
                            'question_id' => $question->id
                        ];
                    }

                    $technicalSkillSections[] = [
                        'title' => $subCategory->subcategory_name,
                        'skills' => $sectionSkills
                    ];
                }
            }

            return response()->json($technicalSkillSections);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve technical skills data'], 500);
        }
    }

    /**
     * Get soft skills data from database
     */
    public function getSoftSkills()
    {
        try {
            $softCategory = Category::where('category_name', 'Soft Skill')->first();

            if (!$softCategory) {
                return response()->json(['error' => 'Soft Skill category not found'], 404);
            }

            $subCategories = SubCategory::with(['questions' => function ($query) {
                $query->where('is_active', true);
            }])->where('category_id', $softCategory->id)->get();

            $softSkillSections = [];

            foreach ($subCategories as $subCategory) {
                if ($subCategory->questions->count() > 0) {
                    $sectionSkills = [];
                    foreach ($subCategory->questions as $question) {
                        $sectionSkills[] = [
                            'name' => strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subCategory->subcategory_name)) . '_' . $question->id,
                            'label' => $question->question,
                            'subcategory_id' => $subCategory->id,
                            'question_id' => $question->id
                        ];
                    }

                    $softSkillSections[] = [
                        'title' => $subCategory->subcategory_name,
                        'skills' => $sectionSkills
                    ];
                }
            }

            return response()->json($softSkillSections);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve soft skills data'], 500);
        }
    }

    /**
     * Get student profile data with scores
     */
    public function getStudentProfile()
    {
        try {
            $user = Auth::user();
            $student = Student::with('section')->where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Get all categories with their subcategories and scores
            $categories = Category::with(['subCategories' => function ($query) use ($student) {
                $query->with(['studentScores' => function ($scoreQuery) use ($student) {
                    $scoreQuery->where('student_id', $student->id);
                }]);
            }])->get();

            // Get student's additional info
            $additionalInfoData = [];
            $studentAdditionalInfos = StudentAdditionalInfo::with('additionalInfo')
                ->where('student_id', $student->id)
                ->get();
            
            foreach ($studentAdditionalInfos as $studentInfo) {
                $additionalInfoData[] = [
                    'info_name' => $studentInfo->additionalInfo->info_name,
                    'info_value' => $studentInfo->info,
                ];
            }

            $profileData = [
                'student' => [
                    'id' => $student->id,
                    'student_number' => $student->student_number,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'middle_name' => $student->middle_name,
                    'phone' => $student->phone,
                    'section' => $student->section->section_name ?? '',
                    'specialization' => $student->specialization,
                    'address' => $student->address,
                    'birth_date' => $student->birth_date,
                    'is_submit' => $student->is_submit,
                ],
                'categories' => [],
                'additional_info' => $additionalInfoData,
            ];

            foreach ($categories as $category) {
                $categoryData = [
                    'id' => $category->id,
                    'name' => $category->category_name,
                    'subcategories' => []
                ];

                foreach ($category->subCategories as $subcategory) {
                    $score = $subcategory->studentScores->first();
                    $categoryData['subcategories'][] = [
                        'id' => $subcategory->id,
                        'name' => $subcategory->subcategory_name,
                        'score' => $score ? round($score->score, 2) : 0,
                    ];
                }

                $profileData['categories'][] = $categoryData;
            }

            return response()->json($profileData);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve student profile data'], 500);
        }
    }

    /**
     * Get student dashboard data
     */
    public function dashboard()
    {
        try {
            $user = Auth::user();
            $student = Student::with('section')->where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Get student's assessment submission status
            $hasSubmitted = $student->is_submit;

            // Get student's scores by category
            $categories = Category::with(['subCategories' => function ($query) use ($student) {
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

            // Get possible internships with compatibility scores (if student has submitted assessment)
            $possibleInternships = collect();
            if ($hasSubmitted) {
                // Debug: Check if student has scores
                $studentScores = $student->scores()->count();
                $activeInternships = \App\Models\Internship::where('is_active', true)->where('slot_count', '>', 0)->count();
                
                \Log::info('Student assessment debug', [
                    'student_id' => $student->id,
                    'has_submitted' => $hasSubmitted,
                    'student_scores_count' => $studentScores,
                    'active_internships_count' => $activeInternships
                ]);
                
                $matchingService = new MatchingService();
                // Calculate and store all compatibility scores for this student
                $matchingService->calculateAndStoreCompatibilityScores($student);
                // Get top 5 for dashboard display
                $possibleInternships = $matchingService->getTopCompatibleInternships($student, 5);
                
                // Fallback: If no matches found, show active internships
                if ($possibleInternships->isEmpty()) {
                    \Log::info('No matches found, showing fallback internships');
                    $fallbackInternships = \App\Models\Internship::with(['hte:id,company_name'])
                        ->where('is_active', true)
                        ->where('slot_count', '>', 0)
                        ->take(5)
                        ->get()
                        ->map(function ($internship) {
                            return [
                                'internship' => $internship,
                                'compatibility_score' => 50, // Default compatibility score
                                'rank' => 1,
                            ];
                        });
                    $possibleInternships = $fallbackInternships;
                }
                
                // Debug logging
                \Log::info('Possible internships for student ' . $student->id, [
                    'count' => $possibleInternships->count(),
                    'data' => $possibleInternships->toArray()
                ]);
            }

            // Get student's current placement status (if any)
            $currentPlacement = null;
            if ($hasSubmitted) {
                $currentPlacement = \App\Models\StudentPlacement::where('student_id', $student->id)
                    ->where('status', 'approved')
                    ->with(['internship.hte:id,company_name'])
                    ->first();
            }

            // Get student's additional info (if submitted)
            $additionalInfoData = [];
            if ($hasSubmitted) {
                $studentAdditionalInfos = StudentAdditionalInfo::with('additionalInfo')
                    ->where('student_id', $student->id)
                    ->get();
                
                foreach ($studentAdditionalInfos as $studentInfo) {
                    $additionalInfoData[] = [
                        'info_name' => $studentInfo->additionalInfo->info_name,
                        'info_value' => $studentInfo->info,
                    ];
                }
            }

            $dashboardData = [
                'student' => [
                    'id' => $student->id,
                    'student_number' => $student->student_number,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'middle_name' => $student->middle_name,
                    'section' => $student->section->section_name ?? '',
                    'specialization' => $student->specialization,
                    'has_submitted_assessment' => $hasSubmitted,
                ],
                'performance' => [
                    'overall_average' => $overallAverage,
                    'total_questions' => $totalQuestions,
                    'category_scores' => $categoryScores,
                ],
                'possible_internships' => $possibleInternships->map(function ($item) {
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
                })->toArray(),
                'current_match' => $currentPlacement ? [
                    'id' => $currentPlacement->id,
                    'internship' => [
                        'position_title' => $currentPlacement->internship->position_title,
                        'company_name' => $currentPlacement->internship->hte->company_name,
                    ],
                    'match_score' => $currentPlacement->compatibility_score ?? 0,
                    'status' => $currentPlacement->status ?? 'pending',
                ] : null,
                'additional_info' => $additionalInfoData,
            ];

            return response()->json($dashboardData);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve dashboard data'], 500);
        }
    }

    /**
     * Notify admins when a student completes their assessment
     */
    private function notifyAdminsForStudentAssessmentCompletion(Student $student): void
    {
        try {
            $notificationService = new NotificationService();
            $notificationService->notifyAdminsForStudentAssessmentCompletion($student);
            
            \Log::info('Admin notification sent for student assessment completion', [
                'student_id' => $student->id,
                'student_name' => "{$student->first_name} {$student->last_name}"
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send admin notification for student assessment completion', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
