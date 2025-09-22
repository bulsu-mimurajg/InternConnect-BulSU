<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentMatch;
use App\Models\Endorsement;
use App\Models\Deadline;
use Illuminate\Support\Facades\Log;

class AutomaticEndorsementService
{
    /**
     * Automatically endorse students based on SIP endorsement deadline
     */
    public function processSipEndorsements(): array
    {
        $results = [
            'endorsed_count' => 0,
            'errors' => [],
            'skipped_count' => 0
        ];

        try {
            // Check if SIP endorsement deadline has passed or is about to expire (1 minute before)
            $deadline = Deadline::where('category', 'sip_endorsement')
                ->where(function($query) {
                    $query->where('status', 'expired')
                        ->orWhere(function($q) {
                            $q->where('status', 'active')
                                ->where('end_date', '<=', now()->addMinute()); // 1 minute before expiry
                        });
                })
                ->first();

            if (!$deadline) {
                Log::info('SIP endorsement deadline not found, not expired, or not about to expire');
                return $results;
            }

            Log::info('SIP endorsement deadline expired, processing automatic endorsements');

            // Get all students who have submitted assessments and are not yet placed
            $students = Student::with(['compatibilityScores.internship.hte'])
                ->where('is_submit', true)
                ->where('is_active', true)
                ->where('is_placed', false)
                ->whereDoesntHave('endorsements', function($q) {
                    $q->where('status', 'endorsed');
                })
                ->get();

            foreach ($students as $student) {
                try {
                    // Iterate through student's matches by highest compatibility
                    $matches = $student->compatibilityScores()
                        ->with(['internship.hte'])
                        ->where('endorsement_status', 'pending')
                        ->orderBy('compatibility_score', 'desc')
                        ->get();

                    if ($matches->isEmpty()) {
                        $results['skipped_count']++;
                        Log::warning("No pending matches found for student {$student->id}");
                        continue;
                    }

                    $endorsed = false;
                    foreach ($matches as $candidateMatch) {
                        // Check available slots for the candidate internship
                        $availableSlots = $candidateMatch->internship->slot_count -
                            $candidateMatch->internship->studentPlacements()->where('status', 'approved')->count();

                        if ($availableSlots > 0) {
                            // Endorse the student to this available internship
                            $this->endorseStudent($student, $candidateMatch);
                            $results['endorsed_count']++;
                            $endorsed = true;
                            Log::info("Automatically endorsed student {$student->id} for internship {$candidateMatch->internship->id} (slots available)");
                            break;
                        }
                    }

                    if (!$endorsed) {
                        // No internships with available slots found; skip for now
                        $results['skipped_count']++;
                        Log::warning("No internships with available slots for student {$student->id}; skipping auto-endorsement");
                    }

                } catch (\Exception $e) {
                    $error = "Failed to endorse student {$student->id}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    Log::error($error, [
                        'student_id' => $student->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

        } catch (\Exception $e) {
            $error = "SIP endorsement processing failed: " . $e->getMessage();
            $results['errors'][] = $error;
            Log::error($error, [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Endorse a student for their best match
     */
    private function endorseStudent(Student $student, StudentMatch $match): void
    {
        // Update the student_match record endorsement status to 'endorsed'
        StudentMatch::where('student_id', $student->id)
            ->where('internship_id', $match->internship_id)
            ->update(['endorsement_status' => 'endorsed']);

        // Create endorsement record
        Endorsement::create([
            'student_id' => $student->id,
            'internship_id' => $match->internship_id,
            'status' => 'endorsed',
            'compatibility_score' => $match->compatibility_score,
            'endorsement_date' => now(),
        ]);
    }
}
