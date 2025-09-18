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
use App\Services\MatchingService;
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

        return Inertia::render('student/assessment', [
            'hasSubmitted' => $hasSubmitted,
            'deadlineActive' => $deadlineActive,
            'deadlineInfo' => $deadlineInfo,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Check if student assessment form deadline is active
        if (!\App\Models\Deadline::isActiveForCategory('student_assessment_form')) {
            return redirect()->back()->withErrors(['error' => 'No current Deadline or Deadline is expired. You cannot submit assessments at this time.']);
        }

        // Get all questions from database to build dynamic validation rules
        $questions = Question::where('access', 'Student')->where('is_active', true)->get();

        // Add validation rules for each question
        foreach ($questions as $question) {
            $subcategory = $question->subcategory;
            $fieldName = strtolower(str_replace(['+', '/', ' ', '-'], ['plus', '_', '_', '_'], $subcategory->subcategory_name)) . '_' . $question->id;
            $validationRules[$fieldName] = 'required|integer|min:1|max:5';
        }

        // Validate the request
        $request->validate($validationRules);

        try {
            // Get the authenticated user
            $user = Auth::user();

            // Find or create student record
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                // Create a new student record if it doesn't exist
                $student = Student::create([
                    'user_id' => $user->id,
                ]);
            }

            // Store assessment data in session for now (or you can store in a different way)
            $assessmentData = [
                'student_id' => $student->id,
                'personal_info' => [
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'middle_name' => $request->middleName,
                    'suffix' => $request->suffix,
                    'province' => $request->province,
                    'city' => $request->city,
                    'zip' => $request->zip,
                ],
                'questions' => [],
                'submitted_at' => now(),
            ];

            // Store question responses and compute scores
            $subcategoryScores = [];

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

            // Update student's is_submit status to true
            $student->update(['is_submit' => true]);

            // Store in session for now (you can modify this to store in database later)
            session(['assessment_data' => $assessmentData]);

            return redirect()->route('assessment')->with('success', 'Assessment submitted successfully!');

        } catch (\Exception $e) {
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
                $query->where('access', 'Student')->where('is_active', true);
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
                $query->where('access', 'Student')->where('is_active', true);
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
                $query->where('access', 'Student')->where('is_active', true);
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
                'categories' => []
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
                $matchingService = new MatchingService();
                // Calculate and store all compatibility scores for this student
                $matchingService->calculateAndStoreCompatibilityScores($student);
                // Get top 5 for dashboard display
                $possibleInternships = $matchingService->getTopCompatibleInternships($student, 5);
            }

            // Get student's current placement status (if any)
            $currentPlacement = null;
            if ($hasSubmitted) {
                $currentPlacement = \App\Models\StudentPlacement::where('student_id', $student->id)
                    ->where('status', 'approved')
                    ->with(['internship.hte:id,company_name'])
                    ->first();
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
                }),
                'current_match' => $currentPlacement ? [
                    'id' => $currentPlacement->id,
                    'internship' => [
                        'position_title' => $currentPlacement->internship->position_title,
                        'company_name' => $currentPlacement->internship->hte->company_name,
                    ],
                    'match_score' => $currentPlacement->compatibility_score ?? 0,
                    'status' => $currentPlacement->status ?? 'pending',
                ] : null,
            ];

            return response()->json($dashboardData);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve dashboard data'], 500);
        }
    }
}
