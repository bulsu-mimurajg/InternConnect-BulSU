<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\Student;
use App\Models\Answer;
use Illuminate\Support\Facades\DB;

class QuizScoringService
{
    /**
     * Grade a student's quiz attempt
     *
     * Process 1: Compare student_response with questions to calculate scores
     */
    public function gradeQuizAttempt(QuizAttempt $attempt): QuizAttempt
    {
        $question = $attempt->question()->with('answers')->first();

        if (!$question) {
            throw new \Exception("Question not found for quiz attempt ID: {$attempt->id}");
        }

        // Determine if answer is correct and calculate points
        $isCorrect = $this->checkAnswer($question, $attempt);
        $pointsEarned = $isCorrect ? ($question->points ?? 1) : 0;

        // Update the attempt with grading results
        $attempt->update([
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'submitted_at' => $attempt->submitted_at ?? now(),
        ]);

        return $attempt->fresh();
    }

    /**
     * Check if the student's answer is correct based on question type
     */
    protected function checkAnswer(Question $question, QuizAttempt $attempt): bool
    {
        $questionType = $question->question_type ?? 'multiple_choice';

        return match ($questionType) {
            'multiple_choice', 'true_false', 'identification' => $this->checkMultipleChoiceAnswer($question, $attempt),
            'essay', 'enumeration' => false, // Manual grading required
            default => false,
        };
    }

    /**
     * Check multiple choice, true/false, or identification answer
     */
    protected function checkMultipleChoiceAnswer(Question $question, QuizAttempt $attempt): bool
    {
        // If selected_answer_id is provided, check if that answer is marked correct
        if ($attempt->selected_answer_id) {
            $selectedAnswer = Answer::find($attempt->selected_answer_id);
            return $selectedAnswer && $selectedAnswer->is_correct;
        }

        // If student_answer text is provided, check against correct answers
        if ($attempt->student_answer) {
            $correctAnswers = $question->answers()
                ->where('is_correct', true)
                ->pluck('answer_text')
                ->map(fn($text) => strtolower(trim($text)))
                ->toArray();

            $studentAnswer = strtolower(trim($attempt->student_answer));

            return in_array($studentAnswer, $correctAnswers);
        }

        return false;
    }

    /**
     * Grade all attempts for a student
     */
    public function gradeStudentAttempts(int $studentId, ?int $attemptNumber = null): array
    {
        $query = QuizAttempt::where('student_id', $studentId)
            ->whereNotNull('submitted_at');

        if ($attemptNumber !== null) {
            $query->where('attempt_number', $attemptNumber);
        }

        $attempts = $query->get();
        $results = [];

        foreach ($attempts as $attempt) {
            $results[] = $this->gradeQuizAttempt($attempt);
        }

        return $results;
    }

    /**
     * Calculate total score for a student's quiz attempt
     */
    public function calculateTotalScore(int $studentId, ?int $attemptNumber = null): array
    {
        $query = QuizAttempt::where('student_id', $studentId)
            ->whereNotNull('submitted_at')
            ->whereNotNull('is_correct');

        if ($attemptNumber !== null) {
            $query->where('attempt_number', $attemptNumber);
        }

        $attempts = $query->get();

        $totalPointsEarned = $attempts->sum('points_earned');
        $totalPossiblePoints = $attempts->sum(function ($attempt) {
            return $attempt->question->points ?? 1;
        });

        $percentage = $totalPossiblePoints > 0
            ? ($totalPointsEarned / $totalPossiblePoints) * 100
            : 0;

        return [
            'total_points_earned' => $totalPointsEarned,
            'total_possible_points' => $totalPossiblePoints,
            'percentage' => round($percentage, 2),
            'total_questions' => $attempts->count(),
            'correct_answers' => $attempts->where('is_correct', true)->count(),
            'incorrect_answers' => $attempts->where('is_correct', false)->count(),
        ];
    }

    /**
     * Calculate score by subcategory for a student
     */
    public function calculateScoresBySubcategory(int $studentId, ?int $attemptNumber = null): array
    {
        $query = QuizAttempt::with('question.subcategory')
            ->where('student_id', $studentId)
            ->whereNotNull('submitted_at')
            ->whereNotNull('is_correct');

        if ($attemptNumber !== null) {
            $query->where('attempt_number', $attemptNumber);
        }

        $attempts = $query->get();
        $subcategoryScores = [];

        foreach ($attempts as $attempt) {
            $subcategoryId = $attempt->question->subcategory_id;
            $subcategoryName = $attempt->question->subcategory->subcategory_name ?? 'Unknown';

            if (!isset($subcategoryScores[$subcategoryId])) {
                $subcategoryScores[$subcategoryId] = [
                    'subcategory_id' => $subcategoryId,
                    'subcategory_name' => $subcategoryName,
                    'points_earned' => 0,
                    'possible_points' => 0,
                    'questions_count' => 0,
                    'correct_count' => 0,
                ];
            }

            $subcategoryScores[$subcategoryId]['points_earned'] += $attempt->points_earned;
            $subcategoryScores[$subcategoryId]['possible_points'] += $attempt->question->points ?? 1;
            $subcategoryScores[$subcategoryId]['questions_count']++;

            if ($attempt->is_correct) {
                $subcategoryScores[$subcategoryId]['correct_count']++;
            }
        }

        // Calculate percentages
        foreach ($subcategoryScores as &$score) {
            $score['percentage'] = $score['possible_points'] > 0
                ? round(($score['points_earned'] / $score['possible_points']) * 100, 2)
                : 0;
        }

        return array_values($subcategoryScores);
    }

    /**
     * Update student_score table with calculated scores
     */
    public function updateStudentScores(int $studentId, ?int $attemptNumber = null): void
    {
        $subcategoryScores = $this->calculateScoresBySubcategory($studentId, $attemptNumber);

        foreach ($subcategoryScores as $scoreData) {
            DB::table('student_score')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'sub_category_id' => $scoreData['subcategory_id'],
                ],
                [
                    'score' => $scoreData['percentage'],
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Complete grading process for a student
     * This combines Process 1 (scoring) with updating final scores
     */
    public function completeGrading(int $studentId, ?int $attemptNumber = null): array
    {
        // Grade all attempts
        $gradedAttempts = $this->gradeStudentAttempts($studentId, $attemptNumber);

        // Calculate total score
        $totalScore = $this->calculateTotalScore($studentId, $attemptNumber);

        // Calculate scores by subcategory
        $subcategoryScores = $this->calculateScoresBySubcategory($studentId, $attemptNumber);

        // Update student_score table
        $this->updateStudentScores($studentId, $attemptNumber);

        return [
            'graded_attempts_count' => count($gradedAttempts),
            'total_score' => $totalScore,
            'subcategory_scores' => $subcategoryScores,
        ];
    }
}

