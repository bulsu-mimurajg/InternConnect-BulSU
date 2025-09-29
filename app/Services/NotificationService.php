<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Student;
use App\Models\HTE;
use App\Models\Adviser;
use App\Models\Deadline;

class NotificationService
{
    /**
     * Notify students when assessment is not yet taken
     */
    public function notifyStudentsForPendingAssessment(): void
    {
        $students = Student::where('is_submit', false)
            ->where('is_active', true)
            ->with('user')
            ->get();

        foreach ($students as $student) {
            Notification::createNotification(
                $student->user_id,
                'student_assessment_pending',
                'Assessment Required',
                'You have not yet completed your assessment. Please complete it before the deadline.',
                ['student_id' => $student->id]
            );
        }
    }

    /**
     * Notify students when they have a match
     */
    public function notifyStudentForMatch(Student $student): void
    {
        Notification::createNotification(
            $student->user_id,
            'student_match_found',
            'New Match Found',
            'A new internship match has been found for you. Check your dashboard for details.',
            ['student_id' => $student->id]
        );
    }

    /**
     * Notify students when they get placed/endorsed
     */
    public function notifyStudentForPlacement(Student $student, string $companyName, string $positionTitle, int $internshipId): void
    {
        Notification::createNotification(
            $student->user_id,
            'student_placement',
            'Congratulations! You\'ve Been Placed',
            "Congratulations! You have been placed at {$companyName} for the position: {$positionTitle}. Check your dashboard for more details.",
            [
                'student_id' => $student->id,
                'internship_id' => $internshipId,
                'company_name' => $companyName,
                'position_title' => $positionTitle
            ]
        );
    }

    /**
     * Notify only relevant users when a new deadline is released
     */
    public function notifyNewDeadline(Deadline $deadline): void
    {
        $users = collect();

        // Get only relevant users based on deadline category
        switch ($deadline->category) {
            case 'student_verification':
                $users = User::whereHas('roles', function ($query) {
                    $query->where('name', 'adviser');
                })->get();
                break;
            case 'student_assessment_form':
                $users = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })->get();
                break;
            case 'hte_assessment_form':
                $users = User::whereHas('roles', function ($query) {
                    $query->where('name', 'hte');
                })->get();
                break;
        }

        foreach ($users as $user) {
            $message = match($deadline->category) {
                'student_verification' => "A new student verification deadline has been set: {$deadline->title}. Deadline: " . $deadline->end_date->format('M d, Y H:i'),
                'student_assessment_form' => "A new student assessment deadline has been set: {$deadline->title}. Deadline: " . $deadline->end_date->format('M d, Y H:i'),
                'hte_assessment_form' => "A new HTE assessment deadline has been set: {$deadline->title}. Deadline: " . $deadline->end_date->format('M d, Y H:i'),
                default => 'A new deadline has been set.',
            };

            Notification::createNotification(
                $user->id,
                'deadline_released',
                'New Deadline Released',
                $message,
                ['deadline_id' => $deadline->id, 'category' => $deadline->category]
            );
        }
    }

    /**
     * Notify advisers when there are students to verify
     */
    public function notifyAdvisersForStudentVerification(): void
    {
        $advisers = Adviser::where('is_active', true)
            ->with('user')
            ->get();

        foreach ($advisers as $adviser) {
            // Count pending students in their section
            $pendingCount = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->whereHas('academeAccounts', function ($query) use ($adviser) {
                    $query->where('section_id', $adviser->section_id);
                })
                ->whereDoesntHave('student')
                ->where('status', '!=', 'archived')
                ->count();

            if ($pendingCount > 0) {
                Notification::createNotification(
                    $adviser->user_id,
                    'student_verification_pending',
                    'Students Need Verification',
                    "You have {$pendingCount} student(s) waiting for verification in your section.",
                    ['adviser_id' => $adviser->id, 'pending_count' => $pendingCount]
                );
            }
        }
    }

    /**
     * Notify HTEs when assessment is not yet taken
     */
    public function notifyHTEsForPendingAssessment(): void
    {
        $htes = HTE::where('is_submit', false)
            ->where('is_active', true)
            ->with('user')
            ->get();

        foreach ($htes as $hte) {
            Notification::createNotification(
                $hte->user_id,
                'hte_assessment_pending',
                'HTE Assessment Required',
                'You have not yet completed your HTE assessment form. Please complete it before the deadline.',
                ['hte_id' => $hte->id]
            );
        }
    }

    /**
     * Notify admin when deadline expires
     */
    public function notifyAdminDeadlineExpired(Deadline $deadline): void
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            Notification::createNotification(
                $admin->id,
                'deadline_expired',
                'Deadline Expired',
                "The deadline '{$deadline->title}' has expired.",
                ['deadline_id' => $deadline->id, 'category' => $deadline->category]
            );
        }
    }

    /**
     * Notify admin when students need approval
     */
    public function notifyAdminStudentApprovalNeeded(): void
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        // Count students who have submitted assessments but not placed
        $studentsNeedingApproval = Student::where('is_submit', true)
            ->where('is_active', true)
            ->whereDoesntHave('placements', function ($query) {
                $query->where('status', 'approved');
            })
            ->count();

        if ($studentsNeedingApproval > 0) {
            foreach ($admins as $admin) {
                Notification::createNotification(
                    $admin->id,
                    'student_approval_needed',
                    'Students Need Approval',
                    "You have {$studentsNeedingApproval} student(s) who have completed assessments and need placement approval.",
                    ['admin_id' => $admin->id, 'students_count' => $studentsNeedingApproval]
                );
            }
        }
    }

    /**
     * Notify advisers when someone registers for a section and needs approval
     */
    public function notifyAdviserForSectionRegistration(int $adviserId, int $studentCount): void
    {
        Notification::createNotification(
            $adviserId,
            'student_verification_pending',
            'New Student Registration',
            "You have {$studentCount} new student(s) registered for your section who need approval.",
            ['adviser_id' => $adviserId, 'student_count' => $studentCount]
        );
    }

    /**
     * Notify adviser about individual student approval request
     */
    public function notifyAdviserForStudentApproval(int $adviserId, Student $student): void
    {
        Notification::createNotification(
            $adviserId,
            'student_approval_request',
            'Student Approval Request',
            "Student {$student->first_name} {$student->last_name} ({$student->student_number}) needs your approval for registration.",
            [
                'adviser_id' => $adviserId,
                'student_id' => $student->id,
                'student_name' => "{$student->first_name} {$student->last_name}",
                'student_number' => $student->student_number,
                'section_id' => $student->section_id
            ]
        );
    }

    /**
     * Notify HTE when SIP endorses something
     */
    public function notifyHTEForEndorsement(int $hteId, string $studentName, string $companyName, int $studentId = null, int $internshipId = null): void
    {
        Notification::createNotification(
            $hteId,
            'hte_endorsement',
            'New Student Endorsement',
            "Student {$studentName} has been endorsed for internship at {$companyName}.",
            [
                'hte_id' => $hteId, 
                'student_name' => $studentName, 
                'company_name' => $companyName,
                'student_id' => $studentId,
                'internship_id' => $internshipId
            ]
        );
    }

    /**
     * Notify HTE when assessment deadline is approaching
     */
    public function notifyHTEForApproachingDeadline(int $hteId, string $deadlineTitle, string $deadlineDate): void
    {
        Notification::createNotification(
            $hteId,
            'hte_assessment_pending',
            'Assessment Deadline Approaching',
            "Your HTE assessment deadline '{$deadlineTitle}' is approaching. Due: {$deadlineDate}",
            ['hte_id' => $hteId, 'deadline_title' => $deadlineTitle, 'deadline_date' => $deadlineDate]
        );
    }

    /**
     * Notify student when assessment deadline is approaching
     */
    public function notifyStudentForApproachingDeadline(int $studentId, string $deadlineTitle, string $deadlineDate): void
    {
        Notification::createNotification(
            $studentId,
            'student_assessment_pending',
            'Assessment Deadline Approaching',
            "Your student assessment deadline '{$deadlineTitle}' is approaching. Due: {$deadlineDate}",
            ['student_id' => $studentId, 'deadline_title' => $deadlineTitle, 'deadline_date' => $deadlineDate]
        );
    }

}
