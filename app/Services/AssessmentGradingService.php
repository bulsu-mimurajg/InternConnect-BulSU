<?php

namespace App\Services;

use App\Models\AssessmentGradingCriteria;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AssessmentGradingService
{
    /**
     * Process 2: Apply grading criteria to student scores to determine final grade
     *
     * Compare calculated scores with assessment_weight_criteria to determine final grade
     */
    public function applyGradingCriteria(int $studentId, ?int $categoryId = null, ?int $subcategoryId = null): array
    {
        $student = Student::findOrFail($studentId);
        $results = [];

        if ($subcategoryId) {
            // Grade specific subcategory
            $results[] = $this->gradeSubcategory($student, $subcategoryId);
        } elseif ($categoryId) {
            // Grade all subcategories in category
            $subcategories = DB::table('sub_categories')
                ->where('category_id', $categoryId)
                ->pluck('id');

            foreach ($subcategories as $subId) {
                $results[] = $this->gradeSubcategory($student, $subId);
            }
        } else {
            // Grade all subcategories
            $scores = DB::table('student_score')
                ->where('student_id', $studentId)
                ->get();

            foreach ($scores as $score) {
                $results[] = $this->gradeSubcategory($student, $score->sub_category_id);
            }
        }

        return $results;
    }

    /**
     * Grade a specific subcategory for a student
     */
    protected function gradeSubcategory(Student $student, int $subcategoryId): array
    {
        // Get student's score for this subcategory
        $scoreRecord = DB::table('student_score')
            ->where('student_id', $student->id)
            ->where('sub_category_id', $subcategoryId)
            ->first();

        if (!$scoreRecord) {
            return [
                'student_id' => $student->id,
                'subcategory_id' => $subcategoryId,
                'score' => null,
                'grade' => null,
                'message' => 'No score found for this subcategory',
            ];
        }

        $score = (float) $scoreRecord->score;

        // Get subcategory and category info
        $subcategory = DB::table('sub_categories')->find($subcategoryId);
        $categoryId = $subcategory->category_id ?? null;

        // Find matching criteria
        $criteria = AssessmentGradingCriteria::getCriteriaForScore($score, $categoryId, $subcategoryId);

        if (!$criteria) {
            return [
                'student_id' => $student->id,
                'subcategory_id' => $subcategoryId,
                'subcategory_name' => $subcategory->subcategory_name ?? 'Unknown',
                'score' => $score,
                'grade' => null,
                'message' => 'No grading criteria found for this score',
            ];
        }

        return [
            'student_id' => $student->id,
            'subcategory_id' => $subcategoryId,
            'subcategory_name' => $subcategory->subcategory_name ?? 'Unknown',
            'score' => $score,
            'grade' => [
                'label' => $criteria->grade_label,
                'code' => $criteria->grade_code,
                'grade_point' => $criteria->grade_point,
                'description' => $criteria->description,
                'color_code' => $criteria->color_code,
            ],
            'feedback' => $criteria->getFormattedFeedback([
                'score' => $score,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'subcategory' => $subcategory->subcategory_name ?? 'Unknown',
            ]),
        ];
    }

    /**
     * Calculate overall grade for a student across all subcategories
     */
    public function calculateOverallGrade(int $studentId): array
    {
        // Get all subcategory scores
        $scores = DB::table('student_score')
            ->where('student_id', $studentId)
            ->get();

        if ($scores->isEmpty()) {
            return [
                'student_id' => $studentId,
                'overall_score' => null,
                'overall_grade' => null,
                'message' => 'No scores found for this student',
            ];
        }

        // Calculate average score
        $totalScore = $scores->sum('score');
        $averageScore = $totalScore / $scores->count();

        // Get criteria for average score (use global criteria)
        $criteria = AssessmentGradingCriteria::getCriteriaForScore($averageScore);

        return [
            'student_id' => $studentId,
            'overall_score' => round($averageScore, 2),
            'subcategories_count' => $scores->count(),
            'overall_grade' => $criteria ? [
                'label' => $criteria->grade_label,
                'code' => $criteria->grade_code,
                'grade_point' => $criteria->grade_point,
                'description' => $criteria->description,
                'color_code' => $criteria->color_code,
            ] : null,
            'subcategory_grades' => $this->applyGradingCriteria($studentId),
        ];
    }

    /**
     * Get grade distribution for a category or subcategory
     */
    public function getGradeDistribution(?int $categoryId = null, ?int $subcategoryId = null): array
    {
        $query = DB::table('student_score');

        if ($subcategoryId) {
            $query->where('sub_category_id', $subcategoryId);
        } elseif ($categoryId) {
            $query->whereIn('sub_category_id', function ($q) use ($categoryId) {
                $q->select('id')
                  ->from('sub_categories')
                  ->where('category_id', $categoryId);
            });
        }

        $scores = $query->pluck('score');

        if ($scores->isEmpty()) {
            return [
                'total_students' => 0,
                'distribution' => [],
            ];
        }

        // Get all active criteria
        $criteriaQuery = AssessmentGradingCriteria::active()
            ->orderBy('display_order');

        if ($subcategoryId) {
            $criteriaQuery->forSubcategory($subcategoryId);
        } elseif ($categoryId) {
            $criteriaQuery->forCategory($categoryId);
        }

        $criteriaList = $criteriaQuery->get();

        if ($criteriaList->isEmpty()) {
            $criteriaList = AssessmentGradingCriteria::active()->global()->orderBy('display_order')->get();
        }

        $distribution = [];

        foreach ($criteriaList as $criteria) {
            $count = $scores->filter(function ($score) use ($criteria) {
                return $criteria->containsScore((float) $score);
            })->count();

            $distribution[] = [
                'grade_label' => $criteria->grade_label,
                'grade_code' => $criteria->grade_code,
                'min_score' => $criteria->min_score,
                'max_score' => $criteria->max_score,
                'count' => $count,
                'percentage' => $scores->count() > 0 ? round(($count / $scores->count()) * 100, 2) : 0,
                'color_code' => $criteria->color_code,
            ];
        }

        return [
            'total_students' => $scores->count(),
            'average_score' => round($scores->average(), 2),
            'highest_score' => round($scores->max(), 2),
            'lowest_score' => round($scores->min(), 2),
            'distribution' => $distribution,
        ];
    }

    /**
     * Generate student report card
     */
    public function generateReportCard(int $studentId): array
    {
        $student = Student::with(['user', 'section'])->findOrFail($studentId);

        // Get overall grade
        $overallGrade = $this->calculateOverallGrade($studentId);

        // Get detailed grades by category
        $categories = DB::table('categories')->get();
        $categoryGrades = [];

        foreach ($categories as $category) {
            $subcategoryScores = DB::table('student_score as ss')
                ->join('sub_categories as sc', 'ss.sub_category_id', '=', 'sc.id')
                ->where('ss.student_id', $studentId)
                ->where('sc.category_id', $category->id)
                ->select('ss.sub_category_id', 'ss.score', 'sc.subcategory_name')
                ->get();

            if ($subcategoryScores->isNotEmpty()) {
                $categoryAverage = $subcategoryScores->avg('score');
                $categoryCriteria = AssessmentGradingCriteria::getCriteriaForScore($categoryAverage, $category->id);

                $categoryGrades[] = [
                    'category_id' => $category->id,
                    'category_name' => $category->category_name,
                    'average_score' => round($categoryAverage, 2),
                    'grade' => $categoryCriteria ? [
                        'label' => $categoryCriteria->grade_label,
                        'code' => $categoryCriteria->grade_code,
                        'color_code' => $categoryCriteria->color_code,
                    ] : null,
                    'subcategories' => $subcategoryScores->map(function ($sub) use ($category) {
                        $criteria = AssessmentGradingCriteria::getCriteriaForScore($sub->score, $category->id, $sub->sub_category_id);
                        return [
                            'subcategory_name' => $sub->subcategory_name,
                            'score' => $sub->score,
                            'grade' => $criteria ? $criteria->grade_label : null,
                        ];
                    })->toArray(),
                ];
            }
        }

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_number' => $student->student_number,
                'section' => $student->section->section_name ?? 'N/A',
                'email' => $student->user->email ?? 'N/A',
            ],
            'overall_grade' => $overallGrade,
            'category_grades' => $categoryGrades,
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}

