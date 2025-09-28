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

class AutomaticPlacementService
{
    /**
     * Automatically place students based on HTE placement deadline
     */
    public function processHtePlacements(): array
    {
        $results = [
            'placed_count' => 0,
            'errors' => [],
            'skipped_count' => 0
        ];

        try {
            // Check if HTE placement deadline has passed or is about to expire (1 minute before)
            $deadline = Deadline::where('category', 'student_placements_by_hte')
                ->where(function($query) {
                    $query->where('status', 'expired')
                        ->orWhere(function($q) {
                            $q->where('status', 'active')
                                ->where('end_date', '<=', now()->addMinute()); // 1 minute before expiry
                        });
                })
                ->first();

            if (!$deadline) {
                Log::info('HTE placement deadline not found, not expired, or not about to expire');
                return $results;
            }

            Log::info('HTE placement deadline expired, processing automatic placements');

            // Get all endorsed students who haven't been placed yet
            $endorsedStudents = Endorsement::with(['student', 'internship.hte'])
                ->where('status', 'endorsed')
                ->whereHas('student', function($q) {
                    $q->where('is_placed', false);
                })
                ->get();

            // Group students by internship
            $internshipGroups = $endorsedStudents->groupBy('internship_id');

            foreach ($internshipGroups as $internshipId => $endorsements) {
                try {
                    $this->processInternshipPlacements($internshipId, $endorsements, $results);
                } catch (\Exception $e) {
                    $error = "Failed to process placements for internship {$internshipId}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    Log::error($error, [
                        'internship_id' => $internshipId,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

        } catch (\Exception $e) {
            $error = "HTE placement processing failed: " . $e->getMessage();
            $results['errors'][] = $error;
            Log::error($error, [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Process placements for a specific internship
     */
    private function processInternshipPlacements(int $internshipId, $endorsements, array &$results): void
    {
        $internship = $endorsements->first()->internship;
        $totalSlots = $internship->slot_count;

        // Get current approved placements
        $currentPlacements = StudentPlacement::where('internship_id', $internshipId)
            ->where('status', 'approved')
            ->count();

        $availableSlots = $totalSlots - $currentPlacements;

        if ($availableSlots <= 0) {
            Log::info("No available slots for internship {$internshipId}");
            return;
        }

        // Sort students by their ranking (compatibility score) - highest first
        $sortedEndorsements = $endorsements->sortByDesc(function($endorsement) {
            return $endorsement->compatibility_score;
        });

        $placedStudents = [];
        $remainingStudents = collect();

        // Place students based on ranking
        foreach ($sortedEndorsements as $index => $endorsement) {
            if (count($placedStudents) < $availableSlots) {
                // Place this student
                $this->placeStudent($endorsement);
                $placedStudents[] = $endorsement;
                $results['placed_count']++;

                Log::info("Placed student {$endorsement->student_id} in internship {$internshipId} (rank: " . ($index + 1) . ")");
            } else {
                // No more slots available, try fallback
                $remainingStudents->push($endorsement);
            }
        }

        // Process remaining students with fallback
        foreach ($remainingStudents as $endorsement) {
            try {
                $this->processStudentFallback($endorsement, $results);
            } catch (\Exception $e) {
                $error = "Failed to process fallback for student {$endorsement->student_id}: " . $e->getMessage();
                $results['errors'][] = $error;
                Log::error($error, [
                    'student_id' => $endorsement->student_id,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    /**
     * Place a student in their endorsed internship
     */
    private function placeStudent(Endorsement $endorsement): void
    {
        // Check if placement already exists
        $existingPlacement = StudentPlacement::where('student_id', $endorsement->student_id)
            ->where('internship_id', $endorsement->internship_id)
            ->first();

        if ($existingPlacement) {
            // Update existing placement to approved if not already
            if ($existingPlacement->status !== 'approved') {
                $existingPlacement->update([
                    'status' => 'approved',
                    'placement_date' => now(),
                ]);
            }
            Log::info("Updated existing placement for student {$endorsement->student_id} in internship {$endorsement->internship_id}");
        } else {
            // Create new student placement record
            StudentPlacement::create([
                'student_id' => $endorsement->student_id,
                'internship_id' => $endorsement->internship_id,
                'status' => 'approved',
                'compatibility_score' => $endorsement->compatibility_score,
                'placement_date' => now(),
            ]);
            Log::info("Created new placement for student {$endorsement->student_id} in internship {$endorsement->internship_id}");
        }

        // Update student as placed
        $endorsement->student->update(['is_placed' => true]);

        // Update student_match placement status
        StudentMatch::where('student_id', $endorsement->student_id)
            ->where('internship_id', $endorsement->internship_id)
            ->update(['placement_status' => 'approved']);
    }

    /**
     * Process fallback for a student who couldn't be placed in their endorsed internship
     */
    private function processStudentFallback(Endorsement $endorsement, array &$results): void
    {
        $student = $endorsement->student;

        // Mark the original match as rejected due to full capacity
        StudentMatch::where('student_id', $student->id)
            ->where('internship_id', $endorsement->internship_id)
            ->update(['placement_status' => 'rejected']);

        // Gather all remaining candidate matches ordered by compatibility
        $candidateMatches = StudentMatch::with(['internship.hte'])
            ->where('student_id', $student->id)
            ->where('endorsement_status', 'pending')
            ->orderBy('compatibility_score', 'desc')
            ->get();

        foreach ($candidateMatches as $candidate) {
            // Skip if candidate internship is the same as the one just rejected
            if ((int)$candidate->internship_id === (int)$endorsement->internship_id) {
                continue;
            }

            // Check available slots
            $availableSlots = $candidate->internship->slot_count -
                $candidate->internship->studentPlacements()->where('status', 'approved')->count();

            if ($availableSlots <= 0) {
                continue;
            }

            // Endorse to this fallback internship
            StudentMatch::where('student_id', $student->id)
                ->where('internship_id', $candidate->internship_id)
                ->update(['endorsement_status' => 'endorsed']);

            $newEndorsement = Endorsement::create([
                'student_id' => $student->id,
                'internship_id' => $candidate->internship_id,
                'status' => 'endorsed',
                'compatibility_score' => $candidate->compatibility_score,
                'endorsement_date' => now(),
            ]);

            // Send notification to HTE about the endorsement
            $internship = Internship::with('hte.user')->find($candidate->internship_id);
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

            // Place immediately
            $this->placeStudent($newEndorsement);
            $results['placed_count']++;

            Log::info("Placed student {$student->id} via fallback in internship {$candidate->internship_id}");
            return;
        }

        // No available internships for fallback
        $results['skipped_count']++;
        Log::warning("No available internships for fallback for student {$student->id}");
    }
}
