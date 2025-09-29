<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class StudentPlacementNotificationService
{
    /**
     * Notify student when they are placed
     */
    public function notifyStudentForPlacement(Student $student, array $placementData): void
    {
        try {
            $user = $student->user;
            
            if (!$user) {
                Log::warning("No user found for student {$student->id}");
                return;
            }

            // Create placement notification for the student
            Notification::create([
                'user_id' => $user->id,
                'type' => 'student_placement',
                'title' => 'Placement Confirmed',
                'message' => "Congratulations! You have been placed at {$placementData['company_name']} for your internship.",
                'data' => [
                    'student_id' => $student->id,
                    'placement_id' => $placementData['placement_id'] ?? null,
                    'company_name' => $placementData['company_name'] ?? 'Unknown Company',
                    'hte_id' => $placementData['hte_id'] ?? null,
                    'placement_date' => $placementData['placement_date'] ?? now()->format('M d, Y'),
                    'status' => $placementData['status'] ?? 'confirmed',
                ],
                'is_read' => false,
            ]);

            Log::info("Created placement notification for student {$student->id} at {$placementData['company_name']}");
        } catch (\Exception $e) {
            Log::error("Failed to create placement notification for student {$student->id}: " . $e->getMessage());
        }
    }

    /**
     * Notify student when placement status changes
     */
    public function notifyStudentForPlacementStatusChange(Student $student, string $status, array $additionalData = []): void
    {
        try {
            $user = $student->user;
            
            if (!$user) {
                Log::warning("No user found for student {$student->id}");
                return;
            }

            $statusMessages = [
                'pending' => "Your placement is pending review.",
                'confirmed' => "Your placement has been confirmed!",
                'rejected' => "Your placement has been rejected.",
                'cancelled' => "Your placement has been cancelled.",
                'completed' => "Your placement has been completed.",
            ];

            $message = $statusMessages[$status] ?? "Your placement status has been updated to {$status}.";

            Notification::create([
                'user_id' => $user->id,
                'type' => 'student_placement_status',
                'title' => 'Placement Status Update',
                'message' => $message,
                'data' => [
                    'student_id' => $student->id,
                    'placement_id' => $additionalData['placement_id'] ?? null,
                    'company_name' => $additionalData['company_name'] ?? 'Unknown Company',
                    'hte_id' => $additionalData['hte_id'] ?? null,
                    'status' => $status,
                    'updated_at' => now()->format('M d, Y \a\t g:i A'),
                    'reason' => $additionalData['reason'] ?? null,
                ],
                'is_read' => false,
            ]);

            Log::info("Created placement status notification for student {$student->id}: {$status}");
        } catch (\Exception $e) {
            Log::error("Failed to create placement status notification for student {$student->id}: " . $e->getMessage());
        }
    }

    /**
     * Clean up old student placement notifications
     */
    public function cleanupOldNotifications(): void
    {
        $cutoffDate = now()->subDays(30);
        
        $deletedCount = Notification::whereIn('type', ['student_placement', 'student_placement_status'])
            ->where('created_at', '<', $cutoffDate)
            ->delete();
            
        if ($deletedCount > 0) {
            Log::info("Cleaned up {$deletedCount} old student placement notifications");
        }
    }
}
