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

class AssessmentController extends Controller
{
    /**
     * Handle assessment form submission
     */
    public function store(Request $request): RedirectResponse
    {
        // Get all questions from database to build dynamic validation rules
        $questions = Question::where('access', 'Student')->where('is_active', true)->get();
        
        // Build validation rules dynamically
        $validationRules = [
            'firstName' => 'required|string|max:50',
            'lastName' => 'required|string|max:50',
            'middleName' => 'nullable|string|max:50',
            'suffix' => 'nullable|string|max:10',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:10',
        ];

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
                    'student_number' => 'STU' . $user->id, // Generate a student number
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'middle_name' => $request->middleName,
                    'phone' => '', // Will be filled later
                    'section' => '', // Will be filled later
                    'specialization' => '', // Will be filled later
                    'address' => '', // Will be filled later
                    'birth_date' => now(), // Will be filled later
                ]);
            } else {
                // Update existing student record with personal info
                $student->update([
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'middle_name' => $request->middleName,
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
}
