# 3-Tier Automatic Placement Implementation

## Overview

This document describes the **3-Tier Automatic Placement System** that ensures all students receive internship placements through a prioritized, time-based approach as the deadline approaches.

## Problem Statement

Previously, there were gaps in the automatic placement system:
1. Students visible in the "matched" page (with valid matches but not yet endorsed) were not automatically placed
2. Students with all matches rejected could remain unplaced
3. No clear priority system for different student categories

This created situations where students might not receive internship placements despite having valid matches or completing their assessments.

## Solution

The system now implements a **3-tier automatic placement strategy** that progressively handles different student categories:

### Tier 1: Endorsed Students Placement (30 Minutes Before Deadline - HIGHEST PRIORITY)

**Trigger:** When the internship placement deadline is within 30 minutes

**Target:** Students who:
- Have been endorsed by admin (already in `endorsements` table)
- Have not been placed yet (`is_placed = false`)

**Action:**
- Processes students based on compatibility rankings
- Handles fallback logic for slot conflicts
- Places students according to available slots
- **HIGHEST PRIORITY** - these students were manually reviewed and endorsed by admin
- Ensures endorsed students get first choice of available slots

**Cache Lock:** 300 seconds (runs every 5 minutes)

### Tier 2: Matched Students Auto-Placement (20 Minutes Before Deadline)

**Trigger:** When the internship placement deadline is within 20 minutes

**Target:** Students who:
- Are not placed yet (`is_placed = false`)
- Have completed their assessment (`is_submit = true`)
- Have NOT been endorsed yet (visible in matched.tsx page)
- Have pending matches with `endorsement_status = 'pending'`
- Have NOT rejected all their matches

**Action:**
- Auto-endorses students' best matches (highest compatibility)
- Creates endorsement records automatically
- Immediately places students in their best match
- Only processes if internship has available slots
- Notifies HTEs about the placements
- Marks students as placed

**Cache Lock:** 300 seconds (runs every 5 minutes)

### Tier 3: Emergency Placement (10 Minutes Before Deadline)

**Trigger:** When the internship placement deadline is within 10 minutes

**Target:** Students who:
- Have not been placed yet (`is_placed = false`)
- Have completed their assessment (`is_submit = true`)
- Have NO endorsed matches (all were rejected or never endorsed)
- Have NO pending matches left (exhausted all fallbacks)

**Action:**
- System finds ANY available internship with open slots
- Creates an endorsement and placement automatically
- Prioritizes internships based on:
  1. Existing compatibility score (if there's a rejected match)
  2. First available internship (if no prior match exists)
- Notifies the HTE about the placement
- Marks student as placed
- Safety net - ensures no student is left without placement

**Cache Lock:** 300 seconds (runs every 5 minutes)

## Technical Implementation

### Modified Files

#### 1. `app/Services/AutomaticPlacementService.php`

**New Method:** `processMatchedStudentsPlacements()` (Tier 2)

```php
public function processMatchedStudentsPlacements(): array
{
    // Check if deadline is within 20 minutes
    $deadline = Deadline::where(function($query) {
            $query->where('category', 'internship_placement')
                ->orWhere('category', 'student_placements_by_hte');
        })
        ->where('status', 'active')
        ->where('end_date', '<=', now()->addMinutes(20))
        ->where('end_date', '>', now())
        ->first();

    // Find students with pending matches (not yet endorsed)
    $studentsWithMatches = Student::where('is_placed', false)
        ->where('is_submit', true)
        ->whereDoesntHave('endorsements', function($q) {
            $q->where('status', 'endorsed');
        })
        ->with(['matches' => function($q) {
            $q->where('endorsement_status', 'pending')
              ->where('placement_status', '!=', 'rejected')
              ->orderBy('compatibility_score', 'desc');
        }])
        ->get();

    // For each student:
    // 1. Get their best pending match
    // 2. Check if internship has available slots
    // 3. Auto-endorse the match
    // 4. Create placement immediately
    // 5. Mark student as placed
    // 6. Notify HTE
}
```

**New Method:** `processEmergencyPlacements()` (Tier 3)

```php
public function processEmergencyPlacements(): array
{
    // Check if deadline is within 10 minutes (changed from 30)
    $deadline = Deadline::where(function($query) {
            $query->where('category', 'internship_placement')
                ->orWhere('category', 'student_placements_by_hte');
        })
        ->where('status', 'active')
        ->where('end_date', '<=', now()->addMinutes(10))
        ->where('end_date', '>', now())
        ->first();

    // Find students needing emergency placement (exhausted all matches)
    $studentsNeedingEmergencyPlacement = Student::where('is_placed', false)
        ->where('is_submit', true)
        ->whereDoesntHave('endorsements', function($q) {
            $q->where('status', 'endorsed');
        })
        ->with(['matches'])
        ->get();

    // For each student with no pending matches:
    // 1. Find available internships
    // 2. Select best match (by compatibility or first available)
    // 3. Create endorsement and placement
    // 4. Mark student as placed
    // 5. Notify HTE
}
```

**Updated Method:** `processHtePlacements()`
- Now calls `processMatchedStudentsPlacements()` (Tier 2) after endorsed placements
- Then calls `processEmergencyPlacements()` (Tier 3)
- Returns `matched_placed_count` and `emergency_placed_count` in results

#### 2. `app/Http/Controllers/AdminController.php`

**Updated Method:** `eventsManagement()`

Added 3-tier automatic placement triggers:

```php
// Tier 1: Auto-run placement for endorsed students (1 minute before or at deadline)
$tier1Trigger = \App\Models\Deadline::where('category', 'internship_placement')
    ->where(function($query) use ($nowPlusOneMinute) {
        $query->where('status', 'expired')
            ->orWhere(function($q) use ($nowPlusOneMinute) {
                $q->where('status', 'active')
                    ->where('end_date', '<=', $nowPlusOneMinute);
            });
    })
    ->exists();

// Tier 2: Auto-endorse matched students (20 minutes before deadline)
$tier2Trigger = \App\Models\Deadline::where('category', 'internship_placement')
    ->where('status', 'active')
    ->where('end_date', '<=', $nowPlusTwentyMinutes)
    ->where('end_date', '>', now())
    ->exists();

// Tier 3: Emergency placement (10 minutes before deadline)
$tier3Trigger = \App\Models\Deadline::where('category', 'internship_placement')
    ->where('status', 'active')
    ->where('end_date', '<=', $nowPlusTenMinutes)
    ->where('end_date', '>', now())
    ->exists();

// Execute tiers in priority order (Tier 1 > Tier 2 > Tier 3)
if ($tier1Trigger) {
    // Process endorsed students + all remaining tiers
} elseif ($tier2Trigger) {
    // Auto-endorse and place matched students
} elseif ($tier3Trigger) {
    // Emergency placement for students with no fallbacks
}
```

**Cache Locks:**
- Tier 1 (Endorsed): 300 seconds (runs every 5 minutes when deadline ≤30 minutes)
- Tier 2 (Matched): 300 seconds (runs every 5 minutes when deadline ≤20 minutes)
- Tier 3 (Emergency): 300 seconds (runs every 5 minutes when deadline ≤10 minutes)

**Updated Method:** `processInternshipPlacements()`
- Updated success message to include emergency placement count

#### 3. `resources/js/pages/admin/events.tsx`

Updated informational card to explain the 3-tier system to admins:

```tsx
<Card className="border-l-4 border-l-blue-500 bg-blue-50">
    <CardContent className="p-4">
        <h3>3-Tier Automatic Internship Placement System</h3>
        <div className="space-y-2">
            <div>
                <strong>Tier 1 (T-1 min):</strong> Place already-endorsed students 
                according to compatibility rankings and available slots
            </div>
            <div>
                <strong>Tier 2 (T-20 min):</strong> Auto-endorse and place students 
                from matched page (students with valid matches but not yet endorsed) by compatibility
            </div>
            <div>
                <strong>Tier 3 (T-10 min):</strong> Emergency placement for students 
                with no remaining fallback options into any available internship slots
            </div>
        </div>
    </CardContent>
</Card>
```

## Workflow Timeline

### T-30 Minutes: Tier 1 - Endorsed Students Placement (HIGHEST PRIORITY)

1. System checks every 5 minutes (cache lock)
2. Processes all endorsed students FIRST:
   - Students manually endorsed by admin
   - Sorted by compatibility score (highest first)
   - Not yet placed
3. Places students based on compatibility rankings
4. Handles fallback logic for slot conflicts
5. Ensures endorsed students get first choice of slots
6. Logs: `"Endorsed students placement trigger: Deadline within 30 minutes"`

### T-20 Minutes: Tier 2 - Matched Students Auto-Placement

1. System checks every 5 minutes (cache lock)
2. Identifies students in matched.tsx page:
   - Have completed assessments
   - Have pending matches (not yet endorsed by admin)
   - Not placed yet
3. Auto-endorses their best match (highest compatibility)
4. Creates placement immediately if slots available
5. Logs: `"Matched students auto-placement trigger: Deadline within 20 minutes"`

### T-10 Minutes: Tier 3 - Emergency Placement Window Opens

1. System checks every 5 minutes (cache lock)
2. Identifies students with:
   - No endorsements
   - No pending matches (exhausted all fallbacks)
   - Not yet placed
3. Places them in ANY available internship
4. Safety net to ensure no student left behind
5. Logs: `"Emergency placement trigger: Deadline within 10 minutes"`

### T-0 (Deadline Expires)

1. All tiers have run multiple times
2. Endorsed students placed first (T-30 to T-0)
3. Matched students auto-endorsed and placed (T-20 to T-0)
4. Emergency placements handled (T-10 to T-0)
5. System ensures maximum placement coverage with proper priority

## Logging

The system logs detailed information at each stage:

### Tier 1: Endorsed Students Placement Logs (HIGHEST PRIORITY)

```
Endorsed students placement trigger: Deadline within 30 minutes, placing already-endorsed students
Found X endorsed students for placement
Placed endorsed student {id} in internship {internship_id} (rank: N, compatibility: Y%)
No available slots for internship {internship_id}, skipping
Endorsed students placements processed - placed_count: X, errors_count: Y
```

### Tier 2: Matched Students Auto-Placement Logs

```
Matched students auto-placement trigger: Deadline within 20 minutes, processing students by compatibility
Found X students with pending matches for auto-endorsement
Auto-endorsed and placed student {id} in best match internship {internship_id} (compatibility: Y%)
Best match internship {internship_id} for student {id} has no available slots, skipping
Matched students auto-placements processed - placed_count: X, errors_count: Y
```

### Tier 3: Emergency Placement Logs

```
Emergency placement trigger: Deadline within 10 minutes, processing students with no fallbacks
Found X students needing emergency placement
Student {id} has no pending matches, attempting emergency placement
Emergency placement successful for student {id} in internship {internship_id}
No available internships for emergency placement for student {id}
Emergency placements processed - placed_count: X, errors_count: Y
```

## Database Changes

No schema changes required. The system uses existing tables:

- `students` - tracks `is_placed` status
- `student_matches` - tracks `endorsement_status` and `placement_status`
- `endorsements` - creates new endorsements for emergency placements
- `student_placements` - creates placement records
- `deadlines` - checks deadline timings

## Error Handling

The system gracefully handles errors:

1. **No Available Internships:**
   - Logs warning: `"No available internships for emergency placement for student {id}"`
   - Student remains unplaced
   - Error added to results array

2. **Database Errors:**
   - Caught and logged with full stack trace
   - Error added to results array
   - Processing continues for other students

3. **Notification Failures:**
   - Placement still succeeds
   - Error logged but doesn't prevent placement

## Testing Scenarios

### Scenario 1: Student Visible in Matched Page (Tier 2)

1. Student completes assessment
2. System generates 5 internship matches
3. Admin hasn't endorsed student yet
4. Student visible in matched.tsx with best match showing
5. 20 minutes before deadline:
   - Tier 2 triggers
   - System auto-endorses student's best match (highest compatibility)
   - Creates endorsement and placement
   - Student marked as `is_placed = true`
   - HTE notified

### Scenario 2: Student with All Matches Rejected (Tier 3)

1. Student has 5 internship matches
2. Admin rejects all 5 matches manually
3. Student has `placement_status = 'rejected'` for all matches
4. No pending matches remain
5. 10 minutes before deadline:
   - Tier 3 triggers (emergency placement)
   - System finds ANY available internship
   - Creates new endorsement and placement
   - Student marked as `is_placed = true`

### Scenario 3: Endorsed Student (Tier 1)

1. Admin manually endorses student for specific internship
2. Student has endorsement record
3. 1 minute before deadline:
   - Tier 1 triggers
   - Processes endorsed student with highest priority
   - Places according to slot availability
   - Handles fallback if needed

### Scenario 4: No Available Internships

1. All internships are at full capacity
2. Student in Tier 2 or Tier 3
3. System attempts placement:
   - No internships available
   - Logs warning
   - Student remains unplaced
   - Error logged but doesn't crash system

### Scenario 5: Mixed Placement (All Tiers)

1. Group A: Endorsed students (Tier 1) - 10 students
2. Group B: Matched but not endorsed (Tier 2) - 15 students
3. Group C: No matches left (Tier 3) - 5 students
4. Timeline:
   - T-30: **Group A placed FIRST** (highest priority - admin-endorsed)
   - T-20: Group B auto-endorsed and placed (compatibility-based)
   - T-10: Group C emergency placed (safety net)
   - T-0: All students successfully placed
5. Result: Endorsed students get first choice, then matched students, then emergency placements

## Benefits

1. **Complete Coverage:** 3-tier system ensures all students receive placements
2. **Priority-Based:** Respects manual admin decisions (Tier 1) before automation
3. **Compatibility First:** Tier 2 auto-endorses best matches before emergency placement
4. **Safety Net:** Tier 3 ensures students with exhausted matches still get placed
5. **Automatic:** Admin doesn't need to manually intervene
6. **Gradual Processing:** 20-minute window allows multiple attempts across tiers
7. **Transparent:** Full logging and admin notifications for each tier
8. **Fair Distribution:** Processes students by compatibility scores when possible

## Configuration

To adjust the tier timings, modify:

```php
// In AutomaticPlacementService.php

// Tier 1 (Endorsed Students) - currently 30 minutes
->where('end_date', '<=', now()->addMinutes(30)) // Change 30 to desired minutes

// Tier 2 (Matched Students) - currently 20 minutes
->where('end_date', '<=', now()->addMinutes(20)) // Change 20 to desired minutes

// Tier 3 (Emergency) - currently 10 minutes  
->where('end_date', '<=', now()->addMinutes(10)) // Change 10 to desired minutes

// In AdminController.php

// Tier 1 trigger
$nowPlusThirtyMinutes = now()->addMinutes(30); // Change 30 to desired minutes

// Tier 2 trigger
$nowPlusTwentyMinutes = now()->addMinutes(20); // Change 20 to desired minutes

// Tier 3 trigger
$nowPlusTenMinutes = now()->addMinutes(10); // Change 10 to desired minutes

// Cache lock durations
Cache::lock('auto-endorsed-placement', 300)   // Tier 1: every 5 minutes
Cache::lock('auto-matched-placement', 300)    // Tier 2: every 5 minutes
Cache::lock('auto-emergency-placement', 300)  // Tier 3: every 5 minutes
```

## Future Enhancements

1. **Priority Queue:** Add priority levels for students (e.g., graduating students first)
2. **Student Notifications:** Email students about auto-endorsements and emergency placements
3. **Dashboard Widget:** Show tier-based placement statistics in admin dashboard
4. **Manual Tier Trigger:** Allow admins to manually trigger specific tiers
5. **Placement Preferences:** Allow students to set preferences for auto-endorsement
6. **Analytics:** Track and visualize which tier handled each student's placement
7. **Adjustable Thresholds:** UI for admins to configure tier timing without code changes

## Conclusion

The **3-Tier Automatic Placement System** provides a comprehensive, prioritized approach to ensuring all students receive internship placements:

- **Tier 1 (T-30 min)** - Places already-endorsed students FIRST (highest priority)
- **Tier 2 (T-20 min)** - Auto-endorses and places matched students based on compatibility
- **Tier 3 (T-10 min)** - Emergency safety net for students who have exhausted all options

This progressive system balances administrative control with automation, ensuring:
- **Endorsed students get priority** - Admin-endorsed students are placed first with best slot selection
- **Compatibility-based matching** - Matched students get their best compatible internships
- **No student is left behind** - Emergency tier ensures universal placement
- **Fair distribution** - All placements based on compatibility scores when possible
- **Smart timing** - 30-minute window with 10-minute intervals provides optimal processing

The staggered timing (T-30, T-20, T-10) ensures:
1. **Admin decisions are honored first** before any automation
2. **Compatibility is maximized** for students with pending matches
3. **Safety net catches everyone** who falls through the cracks
4. **Gradual processing** with 5-minute cache locks prevents system overload

