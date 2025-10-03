<?php

namespace App\Services;

use App\Models\Deadline;
use Carbon\Carbon;

class DeadlineStatusService
{
    /**
     * Get current deadline status for admin dashboard
     */
    public function getAdminDeadlineStatus(): array
    {
        $studentAssessmentDeadline = Deadline::getActiveForCategory('student_assessment_form');
        $internshipPlacementDeadline = Deadline::getActiveForCategory('internship_placement');

        return [
            'student_assessment' => $this->formatDeadlineInfo($studentAssessmentDeadline),
            'internship_placement' => $this->formatDeadlineInfo($internshipPlacementDeadline),
            'restrictions' => $this->getActiveRestrictions($studentAssessmentDeadline, $internshipPlacementDeadline),
        ];
    }

    /**
     * Check if specific functionality is restricted
     */
    public function isFunctionalityRestricted(string $functionality): bool
    {
        $studentAssessmentDeadline = Deadline::getActiveForCategory('student_assessment_form');
        $internshipPlacementDeadline = Deadline::getActiveForCategory('internship_placement');

        switch ($functionality) {
            case 'section_management':
                return $studentAssessmentDeadline !== null;
            
            case 'forms_management':
                return $studentAssessmentDeadline !== null;
            
            case 'additional_info_management':
                return $studentAssessmentDeadline !== null;
            
            case 'admin_management':
                return $internshipPlacementDeadline !== null;
            
            default:
                return false;
        }
    }

    /**
     * Get restriction message for specific functionality
     */
    public function getRestrictionMessage(string $functionality): ?string
    {
        $studentAssessmentDeadline = Deadline::getActiveForCategory('student_assessment_form');
        $internshipPlacementDeadline = Deadline::getActiveForCategory('internship_placement');

        switch ($functionality) {
            case 'section_management':
                if ($studentAssessmentDeadline) {
                    return "Section management is disabled during student assessment deadline period. Deadline ends: " . 
                           Carbon::parse($studentAssessmentDeadline->end_date)->format('M j, Y \a\t g:i A');
                }
                break;
            
            case 'forms_management':
                if ($studentAssessmentDeadline) {
                    return "Forms management is disabled during student assessment deadline period. Deadline ends: " . 
                           Carbon::parse($studentAssessmentDeadline->end_date)->format('M j, Y \a\t g:i A');
                }
                break;
            
            case 'additional_info_management':
                if ($studentAssessmentDeadline) {
                    return "Additional info management is disabled during student assessment deadline period. Deadline ends: " . 
                           Carbon::parse($studentAssessmentDeadline->end_date)->format('M j, Y \a\t g:i A');
                }
                break;
            
            case 'admin_management':
                if ($internshipPlacementDeadline) {
                    return "Admin management functions are restricted during internship placement deadline period. Deadline ends: " . 
                           Carbon::parse($internshipPlacementDeadline->end_date)->format('M j, Y \a\t g:i A');
                }
                break;
        }

        return null;
    }

    /**
     * Format deadline information for frontend
     */
    private function formatDeadlineInfo(?Deadline $deadline): ?array
    {
        if (!$deadline) {
            return null;
        }

        $now = Carbon::now();
        $endDate = Carbon::parse($deadline->end_date);
        $timeRemaining = $now->diffInHours($endDate, false);
        $daysRemaining = $now->diffInDays($endDate, false);

        return [
            'id' => $deadline->id,
            'title' => $deadline->title,
            'category' => $deadline->category,
            'end_date' => $deadline->end_date,
            'formatted_end_date' => $endDate->format('M j, Y \a\t g:i A'),
            'time_remaining_hours' => max(0, $timeRemaining),
            'time_remaining_days' => max(0, $daysRemaining),
            'is_active' => $deadline->isActive(),
            'is_expired' => $deadline->isExpired(),
        ];
    }

    /**
     * Get active restrictions based on current deadlines
     */
    private function getActiveRestrictions(?Deadline $studentAssessmentDeadline, ?Deadline $internshipPlacementDeadline): array
    {
        $restrictions = [];

        if ($studentAssessmentDeadline) {
            $restrictions[] = [
                'type' => 'student_assessment',
                'message' => 'Student assessment deadline is active',
                'deadline' => $this->formatDeadlineInfo($studentAssessmentDeadline),
                'affected_functionality' => ['section_management', 'forms_management', 'additional_info_management'],
            ];
        }

        if ($internshipPlacementDeadline) {
            $restrictions[] = [
                'type' => 'internship_placement',
                'message' => 'Internship placement deadline is active',
                'deadline' => $this->formatDeadlineInfo($internshipPlacementDeadline),
                'affected_functionality' => ['admin_management'],
            ];
        }

        return $restrictions;
    }
}
