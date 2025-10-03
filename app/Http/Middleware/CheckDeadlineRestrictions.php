<?php

namespace App\Http\Middleware;

use App\Models\Deadline;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDeadlineRestrictions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $restrictedAction = null): Response
    {
        // Check if there's an active student assessment deadline
        $studentAssessmentDeadline = Deadline::getActiveForCategory('student_assessment_form');
        
        // Check if there's an active internship placement deadline
        $internshipPlacementDeadline = Deadline::getActiveForCategory('internship_placement');

        // Determine which restrictions to apply based on the action
        $restrictions = $this->getRestrictionsForAction($restrictedAction, $studentAssessmentDeadline, $internshipPlacementDeadline);

        if (!empty($restrictions)) {
            // Add deadline information to the request for frontend use
            $request->merge([
                'deadline_restrictions' => $restrictions,
                'active_deadlines' => [
                    'student_assessment' => $studentAssessmentDeadline,
                    'internship_placement' => $internshipPlacementDeadline,
                ]
            ]);

            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Action restricted due to active deadline',
                    'restrictions' => $restrictions,
                    'active_deadlines' => [
                        'student_assessment' => $studentAssessmentDeadline,
                        'internship_placement' => $internshipPlacementDeadline,
                    ]
                ], 423); // 423 Locked status code
            }

            // For web requests, redirect back with error message
            $deadlineInfo = $this->getDeadlineInfoForUser($restrictions);
            return redirect()->back()->withErrors([
                'deadline_restriction' => "This action is currently disabled due to an active deadline: {$deadlineInfo}"
            ]);
        }

        return $next($request);
    }

    /**
     * Get restrictions for a specific action based on active deadlines
     */
    private function getRestrictionsForAction(?string $action, ?Deadline $studentAssessmentDeadline, ?Deadline $internshipPlacementDeadline): array
    {
        $restrictions = [];

        // Section management restrictions
        if (in_array($action, ['section_edit', 'section_archive', 'section_restore'])) {
            if ($studentAssessmentDeadline) {
                $restrictions[] = [
                    'action' => $action,
                    'reason' => 'student_assessment_active',
                    'deadline' => $studentAssessmentDeadline,
                    'message' => 'Section editing and archiving is disabled during student assessment deadline period.'
                ];
            }
        }

        // Forms management restrictions
        if (in_array($action, ['forms_edit', 'forms_archive', 'forms_restore'])) {
            if ($studentAssessmentDeadline) {
                $restrictions[] = [
                    'action' => $action,
                    'reason' => 'student_assessment_active',
                    'deadline' => $studentAssessmentDeadline,
                    'message' => 'Form editing and archiving is disabled during student assessment deadline period.'
                ];
            }
        }

        // Additional info management restrictions
        if (in_array($action, ['additional_info_edit', 'additional_info_archive', 'additional_info_restore'])) {
            if ($studentAssessmentDeadline) {
                $restrictions[] = [
                    'action' => $action,
                    'reason' => 'student_assessment_active',
                    'deadline' => $studentAssessmentDeadline,
                    'message' => 'Additional info editing and archiving is disabled during student assessment deadline period.'
                ];
            }
        }

        // General admin restrictions during internship placement
        if (in_array($action, ['admin_management'])) {
            if ($internshipPlacementDeadline) {
                $restrictions[] = [
                    'action' => $action,
                    'reason' => 'internship_placement_active',
                    'deadline' => $internshipPlacementDeadline,
                    'message' => 'Admin management functions are restricted during internship placement deadline period.'
                ];
            }
        }

        return $restrictions;
    }

    /**
     * Get user-friendly deadline information
     */
    private function getDeadlineInfoForUser(array $restrictions): string
    {
        if (empty($restrictions)) {
            return '';
        }

        $deadline = $restrictions[0]['deadline'];
        $endDate = \Carbon\Carbon::parse($deadline->end_date)->format('M j, Y \a\t g:i A');
        
        return "{$deadline->title} (ends {$endDate})";
    }
}
