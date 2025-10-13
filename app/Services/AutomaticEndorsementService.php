<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentMatch;
use App\Models\StudentPlacement;
use App\Models\Endorsement;
use App\Models\Deadline;
use App\Models\Internship;
use App\Services\NotificationService;
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
            // Check if internship placement deadline has passed or is about to expire (1 minute before)
            // Also check legacy sip_endorsement category for backward compatibility
            $deadline = Deadline::where(function($query) {
                    $query->where('category', 'internship_placement')
                        ->orWhere('category', 'sip_endorsement');
                })
                ->where(function($query) {
                    $query->where('status', 'expired')
                        ->orWhere(function($q) {
                            $q->where('status', 'active')
                                ->where('end_date', '<=', now()->addMinute()); // 1 minute before expiry
                        });
                })
                ->first();

            if (!$deadline) {
                Log::info('Internship placement deadline not found, not expired, or not about to expire');
                return $results;
            }

            Log::info('Internship placement deadline expired, processing automatic endorsements');

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

        // Send notification to HTE about the endorsement
        $internship = Internship::with('hte.user')->find($match->internship_id);
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
        }
    }
    
    /**
     * Process endorsements for matched students (Tier 2)
     */
    public function processMatchedStudentsEndorsement(): array
    {
        $results = [
            'endorsed_count' => 0,
            'errors' => [],
            'skipped_count' => 0
        ];

        try {
            // Get all students who have submitted assessments and are not yet placed
            $students = Student::with(['compatibilityScores.internship.hte'])
                ->where('is_submit', true)
                ->where('is_active', true)
                ->where('is_placed', false)
                ->whereDoesntHave('endorsements', function($q) {
                    $q->where('status', 'endorsed');
                })
                ->get();

            Log::info("Found {$students->count()} matched students for auto-endorsement");

            foreach ($students as $student) {
                try {
                    // Get student's matches by highest compatibility
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
                        // Check available slots
                        $availableSlots = $candidateMatch->internship->slot_count -
                            StudentPlacement::where('internship_id', $candidateMatch->internship_id)
                                ->where('status', 'approved')
                                ->count();

                        if ($availableSlots > 0) {
                            // Endorse the student to this internship
                            $this->endorseStudent($student, $candidateMatch);
                            $results['endorsed_count']++;
                            $endorsed = true;
                            Log::info("Auto-endorsed student {$student->id} for internship {$candidateMatch->internship_id}");
                            break;
                        }
                    }

                    if (!$endorsed) {
                        $results['skipped_count']++;
                        Log::warning("No available slots for student {$student->id} in any matched internship");
                    }

                } catch (\Exception $e) {
                    $error = "Error processing student {$student->id}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    Log::error($error);
                }
            }

        } catch (\Exception $e) {
            $error = "Matched student endorsement processing failed: " . $e->getMessage();
            $results['errors'][] = $error;
            Log::error($error, ['trace' => $e->getTraceAsString()]);
        }

        return $results;
    }
}
