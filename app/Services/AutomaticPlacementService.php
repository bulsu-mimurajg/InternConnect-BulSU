<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentMatch;
use App\Models\StudentPlacement;
use App\Models\Endorsement;
use App\Models\Deadline;
use App\Models\Internship;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Collection;
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
    public function processEndorsedStudentsPlacements(bool $forceRun = false): array
    {
        $results = [
            'placed_count' => 0,
            'errors' => [],
            'skipped_count' => 0,
        ];

        try {
            // Check if automatic placement should run (30 minutes before deadline)
            $deadline = Deadline::where(function($query) {
                    $query->where('category', 'internship_placement')
                        ->orWhere('category', 'student_placements_by_hte');
                })
                ->where('status', 'active')
                ->where('end_date', '<=', now()->addMinutes(30))
                ->where('end_date', '>', now())
                ->first();
            
            // Log trigger type
            if ($forceRun) {
                Log::info('Tier 1: Forced run (season deactivation override)');
            } elseif ($deadline) {
                Log::info('Tier 1: Automatic placement triggered (30 min before deadline)');
            } else {
                Log::info('Tier 1: Manual processing');
            }
            
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

                    $availableSlots = $this->calculateAvailableSlots($internship);

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
     * Uses comprehensive priority system to ensure optimal placement
     */
    public function processMatchedStudentsPlacements(bool $forceRun = false): array
    {
        $results = [
            'matched_placed_count' => 0,
            'errors' => []
        ];

        try {
            // Check if internship placement deadline is within 20 minutes
            if (!$forceRun) {
                $deadline = Deadline::where(function($query) {
                        $query->where('category', 'internship_placement')
                            ->orWhere('category', 'student_placements_by_hte');
                    })
                    ->where('status', 'active')
                    ->where('end_date', '<=', now()->addMinutes(20))
                    ->where('end_date', '>', now())
                    ->first();
                
                if (!$deadline) {
                    Log::info('No active internship placement deadline within 20 minutes, skipping Tier 2');
                    return $results;
                }
                
                Log::info('Tier 2: Automatic placement triggered (20 min before deadline)');
            } else {
                Log::info('Tier 2: Forced run (season deactivation override)');
            }

            Log::info('Tier 2: Processing students by comprehensive priority system');

            // PHASE 1: Collect all valid student-internship combinations
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

            Log::info("Found {$studentsWithMatches->count()} students with pending matches");

            // Build comprehensive candidate list
            $allCandidates = collect();
            
            foreach ($studentsWithMatches as $student) {
                foreach ($student->matches as $match) {
                    // Check if internship has available slots
                    $availableSlots = $this->calculateAvailableSlots($match->internship);
                    
                    if ($availableSlots > 0) {
                        $allCandidates->push([
                            'student' => $student,
                            'match' => $match,
                            'student_id' => $student->id,
                            'internship_id' => $match->internship_id,
                            'compatibility_score' => $match->compatibility_score,
                            'internship' => $match->internship,
                        ]);
                    }
                }
            }

            Log::info("Created {$allCandidates->count()} valid student-internship candidate combinations");

            // Log all candidates for debugging
            Log::debug('All candidates collected:', [
                'candidates' => $allCandidates->map(function($c) {
                    return [
                        'student_id' => $c['student_id'],
                        'internship_id' => $c['internship_id'],
                        'compatibility' => $c['compatibility_score']
                    ];
                })->toArray()
            ]);

            // PHASE 2: Sort by compatibility score (highest first)
            $prioritizedCandidates = $allCandidates->sortByDesc('compatibility_score')->values();

            Log::info("Sorted candidates by compatibility (highest priority first)");

            // Log sorted order for debugging
            Log::debug('Candidates sorted by priority:', [
                'sorted_order' => $prioritizedCandidates->map(function($c, $index) {
                    return [
                        'rank' => $index + 1,
                        'student_id' => $c['student_id'],
                        'internship_id' => $c['internship_id'],
                        'compatibility' => $c['compatibility_score']
                    ];
                })->toArray()
            ]);

            // PHASE 3: Process in priority order with slot tracking
            $placedStudentIds = [];
            $slotUsage = []; // Track how many slots have been allocated per internship
            
            foreach ($prioritizedCandidates as $candidate) {
                $studentId = $candidate['student_id'];
                $internshipId = $candidate['internship_id'];
                
                // Skip if student already placed
                if (in_array($studentId, $placedStudentIds)) {
                    Log::debug("Skipping student {$studentId} - already placed in this batch");
                    continue;
                }
                
                // Initialize slot tracking for this internship
                if (!isset($slotUsage[$internshipId])) {
                    $slotUsage[$internshipId] = 0;
                }
                
                // Check current available slots (accounting for placements made in this batch)
                $initialAvailableSlots = $this->calculateAvailableSlots($candidate['internship']);
                $remainingSlots = $initialAvailableSlots - $slotUsage[$internshipId];
                
                // Skip if no remaining slots
                if ($remainingSlots <= 0) {
                    Log::info("Skipping student {$studentId} for internship {$internshipId} - no remaining slots (initial: {$initialAvailableSlots}, used: {$slotUsage[$internshipId]})");
                    continue;
                }
                
                try {
                    // Auto-endorse this match
                    StudentMatch::where('student_id', $studentId)
                        ->where('internship_id', $internshipId)
                        ->update(['endorsement_status' => 'endorsed']);

                    // Create endorsement
                    $endorsement = Endorsement::create([
                        'student_id' => $studentId,
                        'internship_id' => $internshipId,
                        'status' => 'endorsed',
                        'compatibility_score' => $candidate['compatibility_score'],
                        'endorsement_date' => now(),
                    ]);

                    // Create placement immediately
                    StudentPlacement::create([
                        'student_id' => $studentId,
                        'internship_id' => $internshipId,
                        'status' => 'approved',
                        'compatibility_score' => $candidate['compatibility_score'],
                        'placement_date' => now(),
                    ]);

                    // Mark student as placed
                    $candidate['student']->update(['is_placed' => true]);

                    // Update match status
                    StudentMatch::where('student_id', $studentId)
                        ->where('internship_id', $internshipId)
                        ->update(['placement_status' => 'approved']);

                    // Update endorsement to approved status
                    $endorsement->update(['status' => 'approved']);

                    // Send notification to HTE
                    if ($candidate['internship']->hte && $candidate['internship']->hte->user) {
                        $notificationService = new NotificationService();
                        $studentName = $candidate['student']->first_name . ' ' . $candidate['student']->last_name;
                        $companyName = $candidate['internship']->hte->company_name;
                        $notificationService->notifyHTEForEndorsement(
                            $candidate['internship']->hte->user_id,
                            $studentName,
                            $companyName,
                            $studentId,
                            $internshipId
                        );
                    }

                    // Track placement
                    $placedStudentIds[] = $studentId;
                    $slotUsage[$internshipId]++;
                    $results['matched_placed_count']++;

                    Log::info("Priority placement: Student {$studentId} → Internship {$internshipId} (compatibility: {$candidate['compatibility_score']}%)");

                } catch (\Exception $e) {
                    $error = "Failed to auto-place student {$studentId}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    Log::error($error, [
                        'student_id' => $studentId,
                        'internship_id' => $internshipId,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info("Tier 2 complete: Placed {$results['matched_placed_count']} students using priority system");

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
     * Emergency placement for ALL students with submitted assessments (10 minutes before deadline)
     * Uses comprehensive priority system to ensure optimal placement even in emergency scenarios
     */
    public function processEmergencyPlacements(bool $forceRun = false): array
    {
        $results = [
            'emergency_placed_count' => 0,
            'errors' => []
        ];

        try {
            // Check if emergency placement should run (10 minutes before deadline)
            if (!$forceRun) {
                $deadline = Deadline::where(function($query) {
                        $query->where('category', 'internship_placement')
                            ->orWhere('category', 'student_placements_by_hte');
                    })
                    ->where('status', 'active')
                    ->where('end_date', '<=', now()->addMinutes(10))
                    ->where('end_date', '>', now())
                    ->first();
                
                if (!$deadline) {
                    Log::info('No active deadline within 10 minutes, skipping Tier 3');
                    return $results;
                }
                
                Log::info('Tier 3: Emergency placement triggered (10 min before deadline)');
            } else {
                Log::info('Tier 3: Forced run (season deactivation override)');
            }

            Log::info('Tier 3: Processing emergency placements using comprehensive priority system');

            // Find ALL students who:
            // 1. Are not placed yet
            // 2. Have completed their assessment (is_submit = true)
            $studentsNeedingEmergencyPlacement = Student::where('is_placed', false)
                ->where('is_submit', true)
                ->with(['scores.subcategory'])
                ->get();

            Log::info("Found {$studentsNeedingEmergencyPlacement->count()} students needing emergency placement");

            if ($studentsNeedingEmergencyPlacement->isEmpty()) {
                Log::info('No students need emergency placement');
                return $results;
            }

            // Get all active internships with available slots
            $availableInternships = Internship::where('is_active', true)
                ->with(['questionImportanceRatings.question.subcategory', 'hte'])
                ->get()
                ->filter(function($internship) {
                    return $this->calculateAvailableSlots($internship) > 0;
                });

            if ($availableInternships->isEmpty()) {
                Log::warning('No available internships for emergency placement');
                
                // Create records for admin visibility
                foreach ($studentsNeedingEmergencyPlacement as $student) {
                    \App\Models\UnplacedStudent::updateOrCreate(
                        ['student_id' => $student->id],
                        [
                            'reason' => 'No available internship slots',
                            'requires_manual_intervention' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
                
                return $results;
            }

            // PHASE 1: Build comprehensive candidate list for all student-internship combinations
            $allCandidates = collect();
            
            foreach ($studentsNeedingEmergencyPlacement as $student) {
                // Calculate fresh compatibility scores for all available internships
                $internshipsWithScores = $this->calculateFreshCompatibilityScores($student, $availableInternships);
                
                foreach ($internshipsWithScores as $match) {
                    $internship = $match['internship'];
                    $compatibilityScore = $match['compatibility_score'];
                    
                    // Check if internship has available slots
                    $availableSlots = $this->calculateAvailableSlots($internship);
                    
                    if ($availableSlots > 0) {
                        $allCandidates->push([
                            'student' => $student,
                            'internship' => $internship,
                            'student_id' => $student->id,
                            'internship_id' => $internship->id,
                            'compatibility_score' => $compatibilityScore,
                        ]);
                    }
                }
            }

            Log::info("Created {$allCandidates->count()} emergency placement candidate combinations");

            // Log all candidates for debugging
            Log::debug('Emergency placement candidates collected:', [
                'candidates' => $allCandidates->map(function($c) {
                    return [
                        'student_id' => $c['student_id'],
                        'internship_id' => $c['internship_id'],
                        'compatibility' => $c['compatibility_score']
                    ];
                })->toArray()
            ]);

            // PHASE 2: Sort by compatibility score (highest first)
            $prioritizedCandidates = $allCandidates->sortByDesc('compatibility_score')->values();

            Log::info("Sorted emergency candidates by compatibility (highest priority first)");

            // Log sorted order for debugging
            Log::debug('Emergency candidates sorted by priority:', [
                'sorted_order' => $prioritizedCandidates->map(function($c, $index) {
                    return [
                        'rank' => $index + 1,
                        'student_id' => $c['student_id'],
                        'internship_id' => $c['internship_id'],
                        'compatibility' => $c['compatibility_score']
                    ];
                })->toArray()
            ]);

            // PHASE 3: Process in priority order with slot tracking
            $placedStudentIds = [];
            $slotUsage = []; // Track how many slots have been allocated per internship
            
            foreach ($prioritizedCandidates as $candidate) {
                $studentId = $candidate['student_id'];
                $internshipId = $candidate['internship_id'];
                
                // Skip if student already placed
                if (in_array($studentId, $placedStudentIds)) {
                    Log::debug("Skipping student {$studentId} - already placed in emergency batch");
                    continue;
                }
                
                // Initialize slot tracking for this internship
                if (!isset($slotUsage[$internshipId])) {
                    $slotUsage[$internshipId] = 0;
                }
                
                // Check current available slots (accounting for placements made in this batch)
                $initialAvailableSlots = $this->calculateAvailableSlots($candidate['internship']);
                $remainingSlots = $initialAvailableSlots - $slotUsage[$internshipId];
                
                // Skip if no remaining slots
                if ($remainingSlots <= 0) {
                    Log::info("Skipping student {$studentId} for internship {$internshipId} - no remaining slots (initial: {$initialAvailableSlots}, used: {$slotUsage[$internshipId]})");
                    continue;
                }
                
                try {
                    // Create or update the match
                    $match = StudentMatch::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'internship_id' => $internshipId,
                        ],
                        [
                            'compatibility_score' => $candidate['compatibility_score'],
                            'endorsement_status' => 'endorsed',
                            'placement_status' => 'approved',
                        ]
                    );

                    // Create endorsement
                    $endorsement = Endorsement::create([
                        'student_id' => $studentId,
                        'internship_id' => $internshipId,
                        'status' => 'endorsed',
                        'compatibility_score' => $candidate['compatibility_score'],
                        'endorsement_date' => now(),
                    ]);

                    // Create placement
                    StudentPlacement::create([
                        'student_id' => $studentId,
                        'internship_id' => $internshipId,
                        'status' => 'approved',
                        'compatibility_score' => $candidate['compatibility_score'],
                        'placement_date' => now(),
                    ]);

                    // Mark student as placed
                    $candidate['student']->update(['is_placed' => true]);

                    // Update the endorsement status to 'approved'
                    $endorsement->update(['status' => 'approved']);

                    // Send notification to HTE
                    if ($candidate['internship']->hte && $candidate['internship']->hte->user) {
                        $notificationService = new NotificationService();
                        $studentName = $candidate['student']->first_name . ' ' . $candidate['student']->last_name;
                        $companyName = $candidate['internship']->hte->company_name;
                        $notificationService->notifyHTEForEndorsement(
                            $candidate['internship']->hte->user_id,
                            $studentName,
                            $companyName,
                            $studentId,
                            $internshipId
                        );
                    }

                    // Track placement
                    $placedStudentIds[] = $studentId;
                    $slotUsage[$internshipId]++;
                    $results['emergency_placed_count']++;

                    Log::info("Emergency priority placement: Student {$studentId} → Internship {$internshipId} (compatibility: {$candidate['compatibility_score']}%)");

                } catch (\Exception $e) {
                    $error = "Failed emergency placement for student {$studentId}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    Log::error($error, [
                        'student_id' => $studentId,
                        'internship_id' => $internshipId,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Handle students who couldn't be placed
            $unplacedStudents = $studentsNeedingEmergencyPlacement->whereNotIn('id', $placedStudentIds);
            foreach ($unplacedStudents as $student) {
                Log::warning("No available internships for emergency placement for student {$student->id}");
                
                \App\Models\UnplacedStudent::updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'reason' => 'No available internship slots',
                        'requires_manual_intervention' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            Log::info("Tier 3 complete: Placed {$results['emergency_placed_count']} students using priority system");

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

        $availableSlots = $this->calculateAvailableSlots($internship);

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

        // Use the standardized fallback logic
        $nextMatch = $this->findNextBestMatch($student, $endorsement->internship_id);

        if ($nextMatch) {
            // Endorse to this fallback internship
            StudentMatch::where('student_id', $student->id)
                ->where('internship_id', $nextMatch->internship_id)
                ->update(['endorsement_status' => 'endorsed']);

            $newEndorsement = Endorsement::create([
                'student_id' => $student->id,
                'internship_id' => $nextMatch->internship_id,
                'status' => 'endorsed',
                'compatibility_score' => $nextMatch->compatibility_score,
                'endorsement_date' => now(),
            ]);

            // Send notification to HTE about the endorsement
            $internship = Internship::with('hte.user')->find($nextMatch->internship_id);
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

            Log::info("Placed student {$student->id} via fallback in internship {$nextMatch->internship_id}");
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
        // Use the standardized fallback logic
        $nextMatch = $this->findNextBestMatch($endorsement->student, $endorsement->internship_id);

        if ($nextMatch) {
            // Place in fallback internship
            StudentPlacement::create([
                'student_id' => $endorsement->student_id,
                'internship_id' => $nextMatch->internship_id,
                'status' => 'approved',
                'compatibility_score' => $nextMatch->compatibility_score,
                'placement_date' => now(),
            ]);

            $endorsement->student->update(['is_placed' => true]);
            $results['placed_count']++;

            Log::info("Placed student {$endorsement->student_id} in fallback internship {$nextMatch->internship_id}");
            return;
        }

        // No fallback available
        $results['skipped_count']++;
        Log::warning("No fallback placement available for student {$endorsement->student_id}");
    }

    /**
     * Calculate available slots for an internship
     * Accounts for: total slots - approved placements - pending endorsed slots
     */
    public function calculateAvailableSlots(Internship $internship): int
    {
        $approvedPlacements = StudentPlacement::where('internship_id', $internship->id)
            ->where('status', 'approved')
            ->count();
        
        $pendingEndorsements = Endorsement::where('internship_id', $internship->id)
            ->where('status', 'endorsed')
            ->whereHas('student', function($q) {
                $q->where('is_placed', false);
            })
            ->count();
        
        return $internship->slot_count - $approvedPlacements - $pendingEndorsements;
    }

    /**
     * Find next best match with available slots for a student
     * Returns StudentMatch or null if no matches available
     */
    public function findNextBestMatch(Student $student, int $excludeInternshipId = null): ?StudentMatch
    {
        $matches = StudentMatch::with(['internship.hte'])
            ->where('student_id', $student->id)
            ->where('endorsement_status', 'pending')
            ->when($excludeInternshipId, function($q) use ($excludeInternshipId) {
                $q->where('internship_id', '!=', $excludeInternshipId);
            })
            ->orderBy('compatibility_score', 'desc')
            ->get();
        
        foreach ($matches as $match) {
            if ($this->calculateAvailableSlots($match->internship) > 0) {
                return $match;
            }
        }
        
        return null;
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
     * Calculate fresh compatibility scores for a student with all available internships
     * Uses question importance ratings to determine subcategory weights
     */
    private function calculateFreshCompatibilityScores(Student $student, Collection $internships): \Illuminate\Support\Collection
    {
        $compatibilityScores = collect();

        // Get student's scores keyed by subcategory ID
        $studentScores = $student->scores->keyBy('sub_category_id');

        foreach ($internships as $internship) {
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
                    $weightedScore = $scorePercentage * ($subcategoryPercentage / 100);

                    $totalScore += $weightedScore;
                    $totalWeight += $subcategoryPercentage;
                }
            }

            // Calculate final compatibility score
            $compatibilityScore = 0;
            if ($totalWeight > 0) {
                $compatibilityScore = round(($totalScore / $totalWeight) * 100, 2);
            }

            $compatibilityScores->push([
                'internship' => $internship,
                'compatibility_score' => $compatibilityScore,
            ]);
        }

        // Sort by compatibility score (highest first)
        return $compatibilityScores->sortByDesc('compatibility_score')->values();
    }
}
