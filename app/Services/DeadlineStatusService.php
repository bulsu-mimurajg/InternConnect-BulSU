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
     * Get deadline status (alias for getAdminDeadlineStatus for compatibility)
     */
    public function getDeadlineStatus(): array
    {
        return $this->getAdminDeadlineStatus();
    }

    /**
     * Check if specific functionality is restricted
     */
    public function isFunctionalityRestricted(string $functionality): bool
    {
        $studentAssessmentDeadline = Deadline::getActiveForCategory('student_assessment_form');
        $internshipPlacementDeadline = Deadline::getActiveForCategory('internship_placement');

        switch ($functionality) {
            case 'section_archive':
            case 'section_restore':
                return $studentAssessmentDeadline !== null;

            case 'section_management':
                return $studentAssessmentDeadline !== null;

            case 'forms_management':
                return $studentAssessmentDeadline !== null;

            case 'additional_info_management':
                return $studentAssessmentDeadline !== null;

            case 'hte_archive':
            case 'hte_restore':
            case 'hte_management':
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
            case 'section_archive':
                if ($studentAssessmentDeadline) {
                    return "Section archiving is disabled during student assessment deadline period. Deadline ends: " .
                           Carbon::parse($studentAssessmentDeadline->end_date)->format('M j, Y \a\t g:i A');
                }
                break;

            case 'section_restore':
                if ($studentAssessmentDeadline) {
                    return "Section restoration is disabled during student assessment deadline period. Deadline ends: " .
                           Carbon::parse($studentAssessmentDeadline->end_date)->format('M j, Y \a\t g:i A');
                }
                break;

            case 'section_management':
                if ($studentAssessmentDeadline) {
                    return "Section archiving and restoration is disabled during student assessment deadline period. Deadline ends: " .
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

            case 'hte_archive':
                if ($studentAssessmentDeadline) {
                    return "HTE archiving is disabled during student assessment deadline period. Deadline ends: " .
                        Carbon::parse($studentAssessmentDeadline->end_date)->format('M j, Y \a\t g:i A');
                }
                break;

            case 'hte_restore':
                if ($studentAssessmentDeadline) {
                    return "HTE restoration is disabled during student assessment deadline period. Deadline ends: " .
                        Carbon::parse($studentAssessmentDeadline->end_date)->format('M j, Y \a\t g:i A');
                }
                break;

            case 'hte_management':
                if ($studentAssessmentDeadline) {
                    return "HTE management functions are restricted";
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
            // Create separate restrictions for different functionalities
            $studentAssessmentRestrictions = [
                'section_archive' => 'Section archiving is disabled during student assessment deadline period.',
                'section_restore' => 'Section restoration is disabled during student assessment deadline period.',
                'forms_management' => 'Forms management is disabled during student assessment deadline period.',
                'additional_info_management' => 'Additional info management is disabled during student assessment deadline period.',
                'hte_archive' => 'HTE archiving is disabled during student assessment deadline period.',
                'hte_restore' => 'HTE restoration is disabled during student assessment deadline period.',
                'hte_management' => 'HTE management functions are restricted during student assessment deadline period.',
            ];

            foreach ($studentAssessmentRestrictions as $functionality => $message) {
                $restrictions[] = [
                    'type' => 'student_assessment',
                    'message' => $message,
                    'deadline' => $this->formatDeadlineInfo($studentAssessmentDeadline),
                    'affected_functionality' => [$functionality],
                ];
            }
        }

        if ($internshipPlacementDeadline) {
            $restrictions[] = [
                'type' => 'internship_placement',
                'message' => 'Admin management functions are restricted during internship placement deadline period.',
                'deadline' => $this->formatDeadlineInfo($internshipPlacementDeadline),
                'affected_functionality' => ['admin_management'],
            ];
        }

        return $restrictions;
    }
}
