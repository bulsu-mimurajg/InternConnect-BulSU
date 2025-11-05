<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Internship;
use App\Models\StudentScore;
use App\Models\SubcategoryWeight;
use App\Models\QuestionImportanceRating;
use App\Models\StudentMatch;
use Illuminate\Support\Collection;

class MatchingService
{
    /**
     * Calculate and store compatibility scores for a student with all available internships
     */
    public function calculateAndStoreCompatibilityScores(Student $student): Collection
    {
        // Get all active internships with available slots
        // Load question importance ratings with questions and subcategories
        $internships = Internship::with([
            'hte:id,company_name',
            'questionImportanceRatings.question.subcategory'
        ])
            ->where('is_active', true)
            ->where('slot_count', '>', 0)
            ->get();

        // Get student's scores
        $studentScores = $student->scores()->with('subcategory')->get()->keyBy('sub_category_id');

        $compatibilityScores = collect();

        foreach ($internships as $internship) {
            $score = $this->calculateInternshipCompatibility($studentScores, $internship);
            
            $compatibilityScores->push([
                'internship' => $internship,
                'compatibility_score' => $score,
            ]);
        }

        // Sort by compatibility score (highest first) and assign ranks
        $rankedScores = $compatibilityScores
            ->sortByDesc('compatibility_score')
            ->values()
            ->map(function ($item, $index) {
                $item['rank'] = $index + 1;
                return $item;
            });

        // Store all scores in the student_matches table
        $this->storeCompatibilityScores($student, $rankedScores);

        return $rankedScores;
    }

    /**
     * Store all compatibility scores for a student
     */
    private function storeCompatibilityScores(Student $student, Collection $rankedScores): void
    {
        // Get existing matches with their statuses BEFORE deletion
        $existingStatuses = StudentMatch::where('student_id', $student->id)
            ->get()
            ->keyBy('internship_id')
            ->map(function ($match) {
                return [
                    'endorsement_status' => $match->endorsement_status,
                    'placement_status' => $match->placement_status,
                ];
            });

        // Delete existing scores for this student
        StudentMatch::where('student_id', $student->id)->delete();

        // Insert all new scores with preserved statuses
        foreach ($rankedScores as $scoreData) {
            $internshipId = $scoreData['internship']->id;
            
            // Use existing statuses if available, otherwise default to pending
            $statuses = $existingStatuses->get($internshipId, [
                'endorsement_status' => 'pending',
                'placement_status' => 'pending',
            ]);

            StudentMatch::create([
                'student_id' => $student->id,
                'internship_id' => $internshipId,
                'compatibility_score' => $scoreData['compatibility_score'],
                'rank' => $scoreData['rank'],
                'endorsement_status' => $statuses['endorsement_status'],
                'placement_status' => $statuses['placement_status'],
            ]);
        }
    }

    /**
     * Update existing student matches to have default status if null
     */
    public function updateExistingMatchesStatus(): void
    {
        StudentMatch::whereNull('endorsement_status')->update(['endorsement_status' => 'pending']);
        StudentMatch::whereNull('placement_status')->update(['placement_status' => 'pending']);
    }

    /**
     * Get all compatibility scores for a student (for dynamic sorting)
     */
    public function getAllCompatibilityScores(Student $student): Collection
    {
        return StudentMatch::where('student_id', $student->id)
            ->with(['internship.hte:id,company_name', 'internship.questionImportanceRatings.question.subcategory'])
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
                    'internship' => $match->internship,
                    'compatibility_score' => $match->compatibility_score,
                    'rank' => $match->rank,
                    'endorsement_status' => $match->endorsement_status,
                    'placement_status' => $match->placement_status,
                ];
            });
    }

    /**
     * Get compatibility scores for a student sorted by a specific field
     */
    public function getCompatibilityScoresSorted(Student $student, string $sortBy = 'compatibility_score', string $sortOrder = 'desc'): Collection
    {
        $query = StudentMatch::where('student_id', $student->id)
            ->with(['internship.hte:id,company_name', 'internship.questionImportanceRatings.question.subcategory']);

        // Apply sorting
        if ($sortBy === 'compatibility_score') {
            $query->orderBy('compatibility_score', $sortOrder);
        } elseif ($sortBy === 'rank') {
            $query->orderBy('rank', $sortOrder);
        } elseif ($sortBy === 'company_name') {
            $query->join('internships', 'student_matches.internship_id', '=', 'internships.id')
                  ->join('htes', 'internships.hte_id', '=', 'hte.id')
                  ->orderBy('hte.company_name', $sortOrder)
                  ->select('student_matches.*');
        } elseif ($sortBy === 'position_title') {
            $query->join('internships', 'student_matches.internship_id', '=', 'internships.id')
                  ->orderBy('internships.position_title', $sortOrder)
                  ->select('student_matches.*');
        }

        return $query->get()
            ->filter(function ($match) {
                // Filter out internships with no available slots
                $availableSlots = $match->internship->slot_count - 
                    $match->internship->studentPlacements()->where('status', 'approved')->count();
                return $availableSlots > 0;
            })
            ->map(function ($match) {
                return [
                    'internship' => $match->internship,
                    'compatibility_score' => $match->compatibility_score,
                    'rank' => $match->rank,
                ];
            });
    }

    /**
     * Calculate subcategory percentage from question ratings
     * Formula: (sum of question ratings) / (number of questions × 5) × 100
     */
    private function calculateSubcategoryPercentage(array $questionRatings, int $questionCount): float
    {
        if ($questionCount === 0) {
            return 0;
        }
        
        $sumOfRatings = array_sum($questionRatings);
        $maxPossibleScore = $questionCount * 5;
        
        return round(($sumOfRatings / $maxPossibleScore) * 100, 2);
    }

    /**
     * Calculate compatibility score for a specific internship
     * Uses question importance ratings to determine subcategory weights
     */
    private function calculateInternshipCompatibility(Collection $studentScores, Internship $internship): float
    {
        $totalScore = 0;
        $totalWeight = 0;

        // Get question importance ratings for this internship
        $ratings = $internship->questionImportanceRatings;
        
        // Group ratings by subcategory
        $subcategoryRatings = [];
        $subcategoryQuestionCounts = [];
        
        foreach ($ratings as $rating) {
            if (!$rating->question || !$rating->question->subcategory) {
                continue;
            }
            
            $subcategoryId = $rating->question->subcategory->id;
            
            if (!isset($subcategoryRatings[$subcategoryId])) {
                $subcategoryRatings[$subcategoryId] = [];
                // Count total questions in this subcategory (including unrated ones)
                $subcategoryQuestionCounts[$subcategoryId] = $rating->question->subcategory->questions()
                    ->where('is_active', true)
                    ->count();
            }
            
            // Only include valid ratings (1-5)
            if ($rating->rating >= 1 && $rating->rating <= 5) {
                $subcategoryRatings[$subcategoryId][] = $rating->rating;
            }
        }

        // Calculate subcategory percentages and use them as weights
        foreach ($subcategoryRatings as $subcategoryId => $questionRatings) {
            $questionCount = $subcategoryQuestionCounts[$subcategoryId];
            
            // Calculate subcategory percentage (this becomes the "weight")
            $subcategoryPercentage = $this->calculateSubcategoryPercentage($questionRatings, $questionCount);
            
            // Skip if no valid ratings or percentage is 0
            if (empty($questionRatings) || $subcategoryPercentage == 0) {
                continue;
            }
            
            // Get student's score for this subcategory
            $studentScore = $studentScores->get($subcategoryId);
            
            if ($studentScore) {
                // Convert student score (1-5 scale) to percentage (0-100)
                $scorePercentage = ($studentScore->score / 5) * 100;
                
                // Apply subcategory percentage as weight to the score
                // The subcategory percentage represents how important this subcategory is
                $weightedScore = $scorePercentage * ($subcategoryPercentage / 100);
                
                $totalScore += $weightedScore;
                $totalWeight += $subcategoryPercentage;
            }
        }

        // Calculate final compatibility score
        if ($totalWeight > 0) {
            return round(($totalScore / $totalWeight) * 100, 2);
        }

        return 0;
    }

    /**
     * Get top N compatible internships for a student
     */
    public function getTopCompatibleInternships(Student $student, int $limit = 5): Collection
    {
        $matches = StudentMatch::where('student_id', $student->id)
            ->with(['internship.hte:id,company_name', 'internship.questionImportanceRatings.question.subcategory'])
            ->orderBy('rank')
            ->get();
            
        // Debug logging
        \Log::info('StudentMatch query results', [
            'student_id' => $student->id,
            'total_matches' => $matches->count(),
            'matches_data' => $matches->map(function($match) {
                $availableSlots = $match->internship->slot_count - 
                    $match->internship->studentPlacements()->where('status', 'approved')->count();
                return [
                    'internship_id' => $match->internship->id,
                    'position_title' => $match->internship->position_title,
                    'company_name' => $match->internship->hte->company_name,
                    'slot_count' => $match->internship->slot_count,
                    'available_slots' => $availableSlots,
                    'compatibility_score' => $match->compatibility_score,
                    'rank' => $match->rank
                ];
            })->toArray()
        ]);
        
        return $matches
            ->filter(function ($match) {
                // Filter out internships with no available slots
                $availableSlots = $match->internship->slot_count - 
                    $match->internship->studentPlacements()->where('status', 'approved')->count();
                return $availableSlots > 0;
            })
            ->take($limit)
            ->map(function ($match) {
                return [
                    'internship' => $match->internship,
                    'compatibility_score' => $match->compatibility_score,
                    'rank' => $match->rank,
                ];
            });
    }

    /**
     * Recalculate and store all compatibility scores for all students
     */
    public function recalculateAllStudentScores(): void
    {
        $students = Student::where('is_submit', true)
            ->where('is_active', true)
            ->get();

        foreach ($students as $student) {
            $this->calculateAndStoreCompatibilityScores($student);
        }
    }
}
