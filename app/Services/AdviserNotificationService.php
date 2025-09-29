<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class AdviserNotificationService
{
    /**
     * Notify adviser when a student registers and needs approval
     */
    public function notifyAdviserForStudentApproval(Student $student): void
    {
        try {
            // Get the adviser for the student's section
            $adviser = $this->getAdviserForSection($student->section_id);
            
            if (!$adviser) {
                Log::warning("No adviser found for section {$student->section_id}");
                return;
            }

            // Create notification for the adviser
            Notification::create([
                'user_id' => $adviser->id,
                'type' => 'student_approval_request',
                'title' => 'New Student Registration',
                'message' => "Student {$student->first_name} {$student->last_name} ({$student->student_number}) has registered and needs approval for section {$student->section->name}.",
                'data' => [
                    'student_id' => $student->id,
                    'student_number' => $student->student_number,
                    'student_name' => "{$student->first_name} {$student->last_name}",
                    'section_id' => $student->section_id,
                    'section_name' => $student->section->name ?? 'Unknown Section',
                    'registration_date' => $student->created_at->format('M d, Y \a\t g:i A'),
                ],
                'is_read' => false,
            ]);

            Log::info("Created student approval notification for adviser {$adviser->id} for student {$student->id}");
        } catch (\Exception $e) {
            Log::error("Failed to create student approval notification: " . $e->getMessage());
        }
    }

    /**
     * Notify adviser when student status changes
     */
    public function notifyAdviserForStudentStatusChange(Student $student, string $action): void
    {
        try {
            // Get the adviser for the student's section
            $adviser = $this->getAdviserForSection($student->section_id);
            
            if (!$adviser) {
                Log::warning("No adviser found for section {$student->section_id}");
                return;
            }

            $actionMessages = [
                'approved' => "Student {$student->first_name} {$student->last_name} has been approved.",
                'rejected' => "Student {$student->first_name} {$student->last_name} has been rejected.",
                'activated' => "Student {$student->first_name} {$student->last_name} has been activated.",
                'deactivated' => "Student {$student->first_name} {$student->last_name} has been deactivated.",
            ];

            $message = $actionMessages[$action] ?? "Student {$student->first_name} {$student->last_name} status has been updated.";

            Notification::create([
                'user_id' => $adviser->id,
                'type' => 'student_status_change',
                'title' => 'Student Status Update',
                'message' => $message,
                'data' => [
                    'student_id' => $student->id,
                    'student_number' => $student->student_number,
                    'student_name' => "{$student->first_name} {$student->last_name}",
                    'section_id' => $student->section_id,
                    'section_name' => $student->section->name ?? 'Unknown Section',
                    'action' => $action,
                    'updated_at' => now()->format('M d, Y \a\t g:i A'),
                ],
                'is_read' => false,
            ]);

            Log::info("Created student status change notification for adviser {$adviser->id} for student {$student->id}");
        } catch (\Exception $e) {
            Log::error("Failed to create student status change notification: " . $e->getMessage());
        }
    }

    /**
     * Get adviser for a specific section
     */
    private function getAdviserForSection(int $sectionId): ?User
    {
        // Try to find adviser by section relationship
        $adviser = User::role('adviser')
            ->where('status', 'verified')
            ->whereHas('adviser', function($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->first();

        if ($adviser) {
            return $adviser;
        }

        // Fallback: get any verified adviser
        return User::role('adviser')
            ->where('status', 'verified')
            ->first();
    }

    /**
     * Clean up old adviser notifications
     */
    public function cleanupOldNotifications(): void
    {
        $cutoffDate = now()->subDays(30);
        
        $deletedCount = Notification::whereIn('type', ['student_approval_request', 'student_status_change'])
            ->where('created_at', '<', $cutoffDate)
            ->delete();
            
        if ($deletedCount > 0) {
            Log::info("Cleaned up {$deletedCount} old adviser notifications");
        }
    }
}
