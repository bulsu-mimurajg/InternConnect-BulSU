<!-- 6b0ed464-0e10-403a-9a34-65e814ab49e2 012c4d07-6eae-437f-982d-c99ef98af0c6 -->
# Fix 3-Tier Automatic Placement Logic Consistency

## Overview

Standardize the automatic placement logic between backend service and frontend to ensure consistent student placement behavior. This implements a hybrid approach where manual admin endorsement is available, but automatic placement runs as backup near deadline with proper timing triggers. **Critical**: Includes season deactivation override to bypass timing checks.

## Current Issues Identified

1. **Timing Inconsistency**: Service methods lack proper timing triggers matching their tier descriptions
2. **Slot Calculation**: Different methods used in service vs frontend
3. **Fallback Logic**: Inconsistent implementation between service and frontend
4. **Emergency Placement**: No frontend representation of Tier 3 logic
5. **Season Deactivation Conflict**: Timing checks would fail when deadlines are expired during season deactivation

## Implementation Plan

### 1. Add Tier 1 Timing Trigger with Override Support

**File**: `app/Services/AutomaticPlacementService.php`

Update `processEndorsedStudentsPlacements()` method signature and add timing logic:

```php
public function processEndorsedStudentsPlacements(bool $forceRun = false): array
{
    $results = ['placed_count' => 0, 'errors' => [], 'skipped_count' => 0];
    
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
        Log::info('Tier 1: Automatic placement (30 min before deadline)');
    } else {
        Log::info('Tier 1: Manual processing');
    }
    
    // Continue with existing logic...
}
```

### 2. Update processMatchedStudentsPlacements() with Override

**File**: `app/Services/AutomaticPlacementService.php`

Update method signature (line 201):

```php
public function processMatchedStudentsPlacements(bool $forceRun = false): array
{
    // Existing timing check at lines 209-217 remains
    // But add forceRun bypass:
    
    if (!$forceRun) {
        $deadline = Deadline::where(...)
            ->where('end_date', '<=', now()->addMinutes(20))
            ->first();
        
        if (!$deadline) {
            Log::info('No active deadline within 20 minutes, skipping Tier 2');
            return ['matched_placed_count' => 0, 'errors' => []];
        }
    } else {
        Log::info('Tier 2: Forced run (season deactivation override)');
    }
    
    // Rest of existing logic...
}
```

### 3. Update processEmergencyPlacements() with Override and Timing

**File**: `app/Services/AutomaticPlacementService.php`

Add `$forceRun` parameter and timing check (line 343):

```php
public function processEmergencyPlacements(bool $forceRun = false): array
{
    $results = [
        'emergency_placed_count' => 0,
        'errors' => []
    ];
    
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
    
    // Rest of existing logic...
}
```

### 4. Fix Season Deactivation Call Order

**File**: `app/Services/InternshipSeasonService.php`

Update `autoDeactivateSeason()` method (lines 484-516) to trigger placement BEFORE expiring deadlines:

```php
private function autoDeactivateSeason(InternshipSeason $season): void
{
    // Get counts BEFORE expiring
    $activeDeadlinesCount = $season->deadlines()->where('status', 'active')->count();
    $hasActivePlacementDeadline = $season->deadlines()
        ->where('category', 'internship_placement')
        ->where('status', 'active')
        ->exists();
    
    // IMPORTANT: Trigger placement BEFORE expiring deadlines
    if ($hasActivePlacementDeadline) {
        Log::info('Triggering automatic placement BEFORE expiring deadlines');
        $this->triggerAutomaticPlacementForSeason($season);
    }
    
    // THEN expire all deadlines
    $expiredCount = $season->deadlines()
        ->whereIn('status', ['active', 'inactive'])
        ->update(['status' => 'expired']);
    
    // Mark season as completed
    $season->update(['status' => 'completed']);
    
    Log::info('Auto-deactivated season', [
        'season_id' => $season->id,
        'active_deadlines_count' => $activeDeadlinesCount,
        'expired_deadlines_count' => $expiredCount,
    ]);
}
```

### 5. Update triggerAutomaticPlacementForSeason() with forceRun

**File**: `app/Services/InternshipSeasonService.php`

Update method to pass `forceRun` flag (lines 333-357):

```php
private function triggerAutomaticPlacementForSeason(InternshipSeason $season): void
{
    $placementService = app(\App\Services\AutomaticPlacementService::class);
    
    // Tier 1: Place endorsed students (with force flag)
    $tier1Results = $placementService->processEndorsedStudentsPlacements(true);
    
    // Tier 2: Auto-endorse and place matched students (with force flag)
    $endorsementService = app(\App\Services\AutomaticEndorsementService::class);
    $tier2EndorseResults = $endorsementService->processMatchedStudentsEndorsement();
    $tier2PlaceResults = $placementService->processEndorsedStudentsPlacements(true);
    
    // Tier 3: Emergency placement (with force flag)
    $tier3Results = $placementService->processEmergencyPlacements(true);
    
    $totalPlaced = $tier1Results['placed_count'] + $tier2PlaceResults['placed_count'] + $tier3Results['emergency_placed_count'];
    
    Log::info('Automatic placement completed for season deactivation', [
        'season_id' => $season->id,
        'total_placed' => $totalPlaced,
    ]);
}
```

### 6. Standardize Slot Availability Calculation

**File**: `app/Services/AutomaticPlacementService.php`

Create helper method (add before line 697):

```php
/**
 * Calculate available slots for an internship
 * Accounts for: total slots - approved placements - pending endorsed slots
 */
private function calculateAvailableSlots(Internship $internship): int
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
```

Update all slot calculation instances:

- Line 139-143 in `processEndorsedStudentsPlacements()`
- Line 257-260 in `processMatchedStudentsPlacements()`
- Line 372-373 in `processEmergencyPlacements()`
- Lines 605-606 in `processStudentFallback()`
- Lines 666-669 in `tryFallbackPlacement()`

Also update `AutomaticEndorsementService.php`:

- Lines 78-79 in `processSipEndorsements()`
- Lines 198-210 in `processMatchedStudentsEndorsement()` - use the helper from AutomaticPlacementService

### 7. Extract and Standardize Fallback Logic

**File**: `app/Services/AutomaticPlacementService.php`

Create unified fallback method:

```php
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
```

Update existing methods:

- `processStudentFallback()` (lines 582-651)
- `tryFallbackPlacement()` (lines 656-692)

### 8. Sync Fallback Logic in Admin Controller

**File**: `app/Http/Controllers/Admin/StudentController.php`

Update rejection endpoints to use standardized fallback:

```php
public function rejectPlacement(Request $request, Student $student)
{
    // Existing validation...
    
    StudentMatch::where('student_id', $student->id)
        ->where('internship_id', $validated['internship_id'])
        ->update(['endorsement_status' => 'rejected']);
    
    // Use standardized fallback
    $placementService = app(\App\Services\AutomaticPlacementService::class);
    $nextMatch = $placementService->findNextBestMatch($student, $validated['internship_id']);
    
    if ($nextMatch) {
        return response()->json([
            'message' => "Student moved to next match...",
            'fallback' => true,
            'new_internship' => [...]
        ]);
    }
    
    return response()->json([
        'message' => 'No more matches available.',
        'fallback' => false
    ]);
}
```

Apply to:

- `rejectEndorsement()` method (lines 2022-2116)
- Batch endorsement conflict detection (lines 1719-1996)

### 9. Update Console Command

**File**: `app/Console/Commands/UpdateDeadlineStatuses.php`

Update `triggerAutomaticPlacement()` method (lines 98-126):

```php
private function triggerAutomaticPlacement(): void
{
    $this->info('Starting 3-tier automatic placement...');
    $placementService = app(\App\Services\AutomaticPlacementService::class);
    
    // Tier 1: Endorsed students (no forceRun - uses timing check)
    $this->info('Tier 1: Processing endorsed students...');
    $tier1Results = $placementService->processEndorsedStudentsPlacements();
    $this->line("  Placed: {$tier1Results['placed_count']}");
    
    // Tier 2: Matched students (no forceRun - uses timing check)
    $this->info('Tier 2: Processing matched students...');
    $tier2Results = $placementService->processMatchedStudentsPlacements();
    $this->line("  Placed: {$tier2Results['matched_placed_count']}");
    
    // Tier 3: Emergency placement (no forceRun - runs at deadline)
    $this->info('Tier 3: Emergency placements...');
    $tier3Results = $placementService->processEmergencyPlacements();
    $this->line("  Placed: {$tier3Results['emergency_placed_count']}");
    
    $total = $tier1Results['placed_count'] + $tier2Results['matched_placed_count'] + $tier3Results['emergency_placed_count'];
    $this->info("Total placed: {$total}");
}
```

### 10. Add Logging Throughout

Add consistent logging in all tier methods:

- Log trigger type (manual/automatic/forced)
- Log slot calculations with details
- Log fallback attempts and results
- Log final placement counts

## Testing Checklist

1. Test manual admin endorsement (no timing restrictions)
2. Test manual admin rejection with fallback
3. Test batch endorsement with conflicts
4. Test Tier 1 automatic trigger at 30 minutes before deadline
5. Test Tier 2 automatic trigger at 20 minutes before deadline
6. Test Tier 3 emergency placement at deadline expiry
7. **Test manual season deactivation triggers all 3 tiers**
8. **Test automatic season deactivation triggers all 3 tiers**
9. Verify slot calculations are consistent across all paths
10. Verify fallback logic works identically in service and controller

## Files Modified

1. `app/Services/AutomaticPlacementService.php` - Add forceRun, helpers, timing
2. `app/Services/AutomaticEndorsementService.php` - Slot calculation standardization
3. `app/Services/InternshipSeasonService.php` - Fix call order, pass forceRun
4. `app/Http/Controllers/Admin/StudentController.php` - Fallback logic sync
5. `app/Console/Commands/UpdateDeadlineStatuses.php` - Verification
6. `resources/js/pages/admin/student/matched.tsx` - Optional UI enhancement

## Critical Notes

- **forceRun parameter**: Bypasses timing checks when season is being deactivated
- **Call order in autoDeactivateSeason()**: Must trigger placement BEFORE expiring deadlines
- **Manual deactivation**: Already has correct order (placement then expire)
- **Automatic deactivation**: Fixed to match manual order

### To-dos

- [ ] Add 30-minute deadline check to processEndorsedStudentsPlacements() while maintaining manual capability
- [ ] Create calculateAvailableSlots() helper method in AutomaticPlacementService
- [ ] Replace all slot calculation instances across services with standardized helper
- [ ] Create findNextBestMatch() method for unified fallback logic
- [ ] Update processStudentFallback() and tryFallbackPlacement() to use new helper
- [ ] Update Admin/StudentController rejection endpoints to use standardized fallback
- [ ] Update processEmergencyPlacements() to use standardized slot calculation
- [ ] Add consistent logging across all tier methods for debugging
- [ ] Test all 3 tiers and manual flows to verify consistency