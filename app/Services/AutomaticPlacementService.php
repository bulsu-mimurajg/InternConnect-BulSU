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
            'skipped_count' => 0,
            'matched_placed_count' => 0,
            'emergency_placed_count' => 0
        ];

        try {
            // Check if internship placement deadline has passed or is about to expire (1 minute before)
            // Also check legacy student_placements_by_hte category for backward compatibility
            $deadline = Deadline::where(function($query) {
                    $query->where('category', 'internship_placement')
                        ->orWhere('category', 'student_placements_by_hte');
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

            Log::info('Internship placement deadline expired, processing automatic placements');

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

            // Process matched students auto-placement (those not yet endorsed)
            $matchedResults = $this->processMatchedStudentsPlacements();
            $results['matched_placed_count'] = $matchedResults['matched_placed_count'];
            $results['placed_count'] += $matchedResults['matched_placed_count'];
            $results['errors'] = array_merge($results['errors'], $matchedResults['errors']);

            // Process emergency placements for students with no remaining fallbacks
            $emergencyResults = $this->processEmergencyPlacements();
            $results['emergency_placed_count'] = $emergencyResults['emergency_placed_count'];
            $results['placed_count'] += $emergencyResults['emergency_placed_count'];
            $results['errors'] = array_merge($results['errors'], $emergencyResults['errors']);

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
     * Place already-endorsed students (30 minutes before deadline - HIGHEST PRIORITY)
     * These are students that admin has manually endorsed
     */
    public function processEndorsedStudentsPlacements(): array
    {
        $results = [
            'placed_count' => 0,
            'errors' => [],
            'skipped_count' => 0,
        ];

        try {
            Log::info('Processing endorsed students for placement');

            // Get all endorsed students who haven't been placed yet
            $endorsedStudents = Endorsement::with(['student', 'internship.hte'])
                ->where('status', 'endorsed')
                ->whereHas('student', function($q) {
                    $q->where('is_placed', false);
                })
                ->get();

            if ($endorsedStudents->isEmpty()) {
                Log::info('No endorsed students found for placement');
                return $results;
            }

            Log::info("Found {$endorsedStudents->count()} endorsed students for placement");

            // Group students by internship
            $internshipGroups = $endorsedStudents->groupBy('internship_id');

            foreach ($internshipGroups as $internshipId => $endorsements) {
                try {
                    $internship = $endorsements->first()->internship;
                    $totalSlots = $internship->slot_count;

                    // Get current approved placements
                    $currentPlacements = StudentPlacement::where('internship_id', $internshipId)
                        ->where('status', 'approved')
                        ->count();

                    $availableSlots = $totalSlots - $currentPlacements;

                    if ($availableSlots <= 0) {
                        Log::info("No available slots for internship {$internshipId}, trying fallback");
                        // Try fallback for these students
                        foreach ($endorsements as $endorsement) {
                            $this->tryFallbackPlacement($endorsement, $results);
                        }
                        continue;
                    }

                    // Sort students by compatibility score - highest first
                    $sortedEndorsements = $endorsements->sortByDesc(function($endorsement) {
                        return $endorsement->compatibility_score;
                    });

                    $placedCount = 0;

                    // Place students based on ranking
                    foreach ($sortedEndorsements as $index => $endorsement) {
                        if ($placedCount < $availableSlots) {
                            // Place this student
                            $this->placeStudent($endorsement);
                            $results['placed_count']++;
                            $placedCount++;

                            Log::info("Placed student {$endorsement->student_id} in internship {$internshipId} (rank: " . ($index + 1) . ")");
                        } else {
                            // No more slots, try fallback
                            $this->tryFallbackPlacement($endorsement, $results);
                        }
                    }

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
            $error = "Endorsed students placement failed: " . $e->getMessage();
            $results['errors'][] = $error;
            Log::error($error, [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Auto-endorse and place students with pending matches (20 minutes before deadline - TIER 2)
     * These are students visible in matched.tsx who haven't been endorsed yet
     */
    public function processMatchedStudentsPlacements(): array
    {
        $results = [
            'matched_placed_count' => 0,
            'errors' => []
        ];

        try {
            // Check if internship placement deadline is within 20 minutes
            $deadline = Deadline::where(function($query) {
                    $query->where('category', 'internship_placement')
                        ->orWhere('category', 'student_placements_by_hte');
                })
                ->where('status', 'active')
                ->where('end_date', '<=', now()->addMinutes(20))
                ->where('end_date', '>', now())
                ->first();

            if (!$deadline) {
                Log::info('No active internship placement deadline within 20 minutes');
                return $results;
            }

            Log::info('Matched students auto-placement trigger: Deadline within 20 minutes, processing students by compatibility');

            // Find students who:
            // 1. Are not placed yet
            // 2. Have completed their assessment
            // 3. Have pending matches (visible in matched page)
            // 4. Are not yet endorsed
            $studentsWithMatches = Student::where('is_placed', false)
                ->where('is_submit', true)
                ->whereDoesntHave('endorsements', function($q) {
                    $q->where('status', 'endorsed');
                })
                ->with(['matches' => function($q) {
                    $q->where('endorsement_status', 'pending')
                      ->where('placement_status', '!=', 'rejected')
                      ->with('internship.hte')
                      ->orderBy('compatibility_score', 'desc');
                }])
                ->get();

            Log::info("Found {$studentsWithMatches->count()} students with pending matches for auto-endorsement");

            foreach ($studentsWithMatches as $student) {
                try {
                    // Get the best pending match
                    $bestMatch = $student->matches->first();
                    
                    if (!$bestMatch) {
                        Log::info("Student {$student->id} has no pending matches, skipping");
                        continue;
                    }

                    // Check if the internship has available slots
                    $currentPlacements = $bestMatch->internship->studentPlacements()
                        ->where('status', 'approved')
                        ->count();
                    $availableSlots = $bestMatch->internship->slot_count - $currentPlacements;

                    if ($availableSlots <= 0) {
                        Log::info("Best match internship {$bestMatch->internship_id} for student {$student->id} has no available slots, skipping");
                        continue;
                    }

                    // Auto-endorse this match
                    StudentMatch::where('student_id', $student->id)
                        ->where('internship_id', $bestMatch->internship_id)
                        ->update(['endorsement_status' => 'endorsed']);

                    // Create endorsement
                    $endorsement = Endorsement::create([
                        'student_id' => $student->id,
                        'internship_id' => $bestMatch->internship_id,
                        'status' => 'endorsed',
                        'compatibility_score' => $bestMatch->compatibility_score,
                        'endorsement_date' => now(),
                    ]);

                    // Create placement immediately
                    StudentPlacement::create([
                        'student_id' => $student->id,
                        'internship_id' => $bestMatch->internship_id,
                        'status' => 'approved',
                        'compatibility_score' => $bestMatch->compatibility_score,
                        'placement_date' => now(),
                    ]);

                    // Mark student as placed
                    $student->update(['is_placed' => true]);

                    // Update match status
                    StudentMatch::where('student_id', $student->id)
                        ->where('internship_id', $bestMatch->internship_id)
                        ->update(['placement_status' => 'approved']);

                    // Update the endorsement status to 'approved' so it doesn't show in endorsed list anymore
                    $endorsement->update(['status' => 'approved']);

                    // Send notification to HTE
                    if ($bestMatch->internship->hte && $bestMatch->internship->hte->user) {
                        $notificationService = new NotificationService();
                        $studentName = $student->first_name . ' ' . $student->last_name;
                        $companyName = $bestMatch->internship->hte->company_name;
                        $notificationService->notifyHTEForEndorsement(
                            $bestMatch->internship->hte->user_id,
                            $studentName,
                            $companyName,
                            $student->id,
                            $bestMatch->internship_id
                        );
                    }

                    $results['matched_placed_count']++;
                    Log::info("Auto-endorsed and placed student {$student->id} in best match internship {$bestMatch->internship_id} (compatibility: {$bestMatch->compatibility_score}%)");

                } catch (\Exception $e) {
                    $error = "Failed to auto-place student {$student->id}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    Log::error($error, [
                        'student_id' => $student->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

        } catch (\Exception $e) {
            $error = "Matched students auto-placement failed: " . $e->getMessage();
            $results['errors'][] = $error;
            Log::error($error, [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Emergency placement for students with no remaining fallbacks (10 minutes before deadline)
     * This is triggered separately and handles students who have exhausted all their matches
     */
    public function processEmergencyPlacements(): array
    {
        $results = [
            'emergency_placed_count' => 0,
            'errors' => []
        ];

        try {
            Log::info('Processing emergency placements for remaining students');

            // Find students who:
            // 1. Are not placed yet
            // 2. Have no endorsed matches (all were rejected or no endorsement exists)
            // 3. Have completed their assessment (is_submit = true)
            // 4. Include HTE-rejected students
            $studentsNeedingEmergencyPlacement = Student::where('is_placed', false)
                ->where('is_submit', true)
                ->where(function($q) {
                    // Include students with no endorsements OR only rejected endorsements
                    $q->whereDoesntHave('endorsements')
                      ->orWhereHas('endorsements', function($subQ) {
                          $subQ->where('status', 'rejected');
                      })->whereDoesntHave('endorsements', function($subQ) {
                          $subQ->where('status', 'endorsed');
                      });
                })
                ->with(['matches' => function($q) {
                    $q->with('internship.hte')->orderBy('compatibility_score', 'desc');
                }])
                ->get();

            Log::info("Found {$studentsNeedingEmergencyPlacement->count()} students needing emergency placement");

            foreach ($studentsNeedingEmergencyPlacement as $student) {
                try {
                    $placed = false;
                    
                    // Check if student has ANY pending matches left
                    $pendingMatches = $student->matches->where('endorsement_status', 'pending');
                    
                    if ($pendingMatches->isEmpty()) {
                        // Student has exhausted all matches, find ANY available internship
                        Log::info("Student {$student->id} has no pending matches, attempting emergency placement");
                        
                        // Get all active internships with available slots
                        $availableInternships = Internship::where('is_active', true)
                            ->get()
                            ->filter(function($internship) {
                                $currentPlacements = $internship->studentPlacements()->where('status', 'approved')->count();
                                return ($internship->slot_count - $currentPlacements) > 0;
                            });

                        if ($availableInternships->isEmpty()) {
                            Log::warning("No available internships for emergency placement for student {$student->id}");
                            
                            // Mark student as requiring manual intervention
                            $student->update([
                                'is_placed' => false,
                                'placement_status' => 'requires_manual_intervention'
                            ]);
                            
                            // Create a record for admin visibility
                            \App\Models\UnplacedStudent::updateOrCreate(
                                ['student_id' => $student->id],
                                [
                                    'reason' => 'No available internship slots',
                                    'requires_manual_intervention' => true,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                            
                            continue;
                        }

                        // Find the best match from available internships based on existing compatibility scores
                        // or create a new match with the first available internship
                        $bestAvailableInternship = null;
                        $bestCompatibilityScore = 0;

                        foreach ($availableInternships as $internship) {
                            // Check if there's an existing match (even if rejected)
                            $existingMatch = StudentMatch::where('student_id', $student->id)
                                ->where('internship_id', $internship->id)
                                ->first();

                            if ($existingMatch) {
                                if ($existingMatch->compatibility_score > $bestCompatibilityScore) {
                                    $bestCompatibilityScore = $existingMatch->compatibility_score;
                                    $bestAvailableInternship = $internship;
                                }
                            } else {
                                // No existing match, this is a new option
                                if (!$bestAvailableInternship) {
                                    $bestAvailableInternship = $internship;
                                    $bestCompatibilityScore = 0; // Default score for emergency placement
                                }
                            }
                        }

                        if ($bestAvailableInternship) {
                            // Create or update the match
                            $match = StudentMatch::updateOrCreate(
                                [
                                    'student_id' => $student->id,
                                    'internship_id' => $bestAvailableInternship->id,
                                ],
                                [
                                    'compatibility_score' => $bestCompatibilityScore,
                                    'endorsement_status' => 'endorsed',
                                    'placement_status' => 'approved',
                                ]
                            );

                            // Create endorsement
                            $endorsement = Endorsement::create([
                                'student_id' => $student->id,
                                'internship_id' => $bestAvailableInternship->id,
                                'status' => 'endorsed',
                                'compatibility_score' => $bestCompatibilityScore,
                                'endorsement_date' => now(),
                            ]);

                            // Create placement
                            StudentPlacement::create([
                                'student_id' => $student->id,
                                'internship_id' => $bestAvailableInternship->id,
                                'status' => 'approved',
                                'compatibility_score' => $bestCompatibilityScore,
                                'placement_date' => now(),
                            ]);

                            // Mark student as placed
                            $student->update(['is_placed' => true]);

                            // Update the endorsement status to 'approved' so it doesn't show in endorsed list anymore
                            $endorsement->update(['status' => 'approved']);

                            // Send notification to HTE
                            if ($bestAvailableInternship->hte && $bestAvailableInternship->hte->user) {
                                $notificationService = new NotificationService();
                                $studentName = $student->first_name . ' ' . $student->last_name;
                                $companyName = $bestAvailableInternship->hte->company_name;
                                $notificationService->notifyHTEForEndorsement(
                                    $bestAvailableInternship->hte->user_id,
                                    $studentName,
                                    $companyName,
                                    $student->id,
                                    $bestAvailableInternship->id
                                );
                            }

                            $results['emergency_placed_count']++;
                            $placed = true;
                            Log::info("Emergency placement successful for student {$student->id} in internship {$bestAvailableInternship->id}");
                        } else {
                            Log::warning("No suitable internship found for emergency placement for student {$student->id}");
                        }
                    }

                    if (!$placed) {
                        // Mark student as unplaced and requiring manual intervention
                        \App\Models\UnplacedStudent::updateOrCreate(
                            ['student_id' => $student->id],
                            [
                                'reason' => 'All internships have no available slots',
                                'requires_manual_intervention' => true,
                            ]
                        );
                        
                        Log::warning("Student {$student->id} marked as unplaced - no available slots in any internship");
                    }

                } catch (\Exception $e) {
                    $error = "Failed emergency placement for student {$student->id}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    Log::error($error, [
                        'student_id' => $student->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

        } catch (\Exception $e) {
            $error = "Emergency placement processing failed: " . $e->getMessage();
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
    
    /**
     * Try fallback placement for an endorsed student
     */
    private function tryFallbackPlacement(Endorsement $endorsement, array &$results): void
    {
        // Get student's other matches sorted by compatibility
        $fallbackMatches = $endorsement->student->compatibilityScores()
            ->with('internship')
            ->where('internship_id', '!=', $endorsement->internship_id)
            ->orderBy('compatibility_score', 'desc')
            ->get();

        foreach ($fallbackMatches as $match) {
            $availableSlots = $match->internship->slot_count -
                StudentPlacement::where('internship_id', $match->internship_id)
                    ->where('status', 'approved')
                    ->count();

            if ($availableSlots > 0) {
                // Place in fallback internship
                StudentPlacement::create([
                    'student_id' => $endorsement->student_id,
                    'internship_id' => $match->internship_id,
                    'status' => 'approved',
                    'compatibility_score' => $match->compatibility_score,
                    'placement_date' => now(),
                ]);

                $endorsement->student->update(['is_placed' => true]);
                $results['placed_count']++;

                Log::info("Placed student {$endorsement->student_id} in fallback internship {$match->internship_id}");
                return;
            }
        }

        // No fallback available
        $results['skipped_count']++;
        Log::warning("No fallback placement available for student {$endorsement->student_id}");
    }
}
